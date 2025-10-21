<?php

namespace modules\logistic\lib;

class Money
{
    // 计算精度（小数点后位数）
    const SCALE = 2;

    // 固定值：首重和续重单位
    const FIRST_WEIGHT = 1;
    const ADDITIONAL_UNIT = 1;

    /**
     * 计算多个商品的快递运费
     * @param array $items 商品数组，包含重量、尺寸和数量信息 
     * @param float $firstPiecePrice 首件价格，默认12
     * @param float $additionalPiecePrice 续件价格，默认5
     * @param float $firstWeightPrice 首重价格，默认12
     * @param float $additionalWeightPrice 续重价格，默认5
     * @return array 返回计算结果
     */
    public static function calc(
        array $items, 
        float $firstPiecePrice = 12,
        float $additionalPiecePrice = 5,
        float $firstWeightPrice = 12,
        float $additionalWeightPrice = 5
    ): array {
        $volumetricDivisor = 6000;
        $totalWeight = '0'; // 总重量
        $totalVolume = '0'; // 总体积
        $totalPieces = '0'; // 总件数
        $hasWeight = false; // 是否有重量信息
        $hasDimensions = false; // 是否有尺寸信息
        $itemsDetail = []; // 商品详情

        // 计算总重量、总体积和总件数
        foreach ($items as $index => $item) {
            $num = self::formatNumber($item['num'] ?? 1); // 商品数量
            $weight = self::formatNumber($item['weight'] ?? null); // 实际重量
            $length = self::formatNumber($item['length'] ?? null); // 长度
            $width = self::formatNumber($item['width'] ?? null); // 宽度
            $height = self::formatNumber($item['height'] ?? null); // 高度

            // 检查是否有有效数量
            if (bccomp($num, '0', self::SCALE) <= 0) {
                continue;
            }

            // 累加件数
            $totalPieces = bcadd($totalPieces, $num, self::SCALE);

            // 处理重量信息
            if ($weight !== null && bccomp($weight, '0', self::SCALE) > 0) {
                $hasWeight = true;
                $itemWeight = bcmul($weight, $num, self::SCALE);
                $totalWeight = bcadd($totalWeight, $itemWeight, self::SCALE);
            }

            // 处理尺寸信息
            $hasItemDimensions = $length !== null && bccomp($length, '0', self::SCALE) > 0 &&
                $width !== null && bccomp($width, '0', self::SCALE) > 0 &&
                $height !== null && bccomp($height, '0', self::SCALE) > 0;

            $itemVolume = '0';
            if ($hasItemDimensions) {
                $hasDimensions = true;
                $itemVolume = bcmul(bcmul($length, $width, self::SCALE), $height, self::SCALE);
                $itemVolume = bcmul($itemVolume, $num, self::SCALE);
                $totalVolume = bcadd($totalVolume, $itemVolume, self::SCALE);
            }

            $itemsDetail[] = [
                'index' => $index + 1,
                'quantity' => (float)$num,
                'weight' => $weight !== null ? (float)$weight : 0,
                'has_weight' => $weight !== null && bccomp($weight, '0', self::SCALE) > 0,
                'dimensions' => $hasItemDimensions ? "{$length}×{$width}×{$height}cm" : '无尺寸',
                'has_dimensions' => $hasItemDimensions,
                'volume' => (float)$itemVolume
            ];
        }

        // 无有效商品时返回默认结果
        if (bccomp($totalPieces, '0', self::SCALE) <= 0) {
            return [
                'items_count' => 0,
                'items_detail' => [],
                'total_pieces' => 0.0,
                'total_weight' => 0.0,
                'total_volume' => 0.0,
                'volumetric_weight' => 0.0,
                'chargeable_weight' => 0.0,
                'piece_fee' => 0.0,
                'weight_fee' => 0.0,
                'total_fee' => 0.0,
                'charge_by' => 'none',
                'has_weight' => false,
                'has_dimensions' => false
            ];
        }

        // 格式化价格参数
        $firstPiecePrice = self::formatNumber($firstPiecePrice);
        $additionalPiecePrice = self::formatNumber($additionalPiecePrice);
        $firstWeightPrice = self::formatNumber($firstWeightPrice);
        $additionalWeightPrice = self::formatNumber($additionalWeightPrice);

        // 计算按件计费的费用
        $pieceFee = self::calculatePieceFee($totalPieces, $firstPiecePrice, $additionalPiecePrice);

        // 计算按重量计费的费用（如果有重量或尺寸信息）
        $weightFee = '0';
        $chargeableWeight = '0';
        $volumetricWeight = '0';

        if ($hasWeight || $hasDimensions) {
            // 计算体积重量（如果有尺寸信息）
            if ($hasDimensions) {
                $volumetricWeight = bcdiv($totalVolume, $volumetricDivisor, self::SCALE);
            }

            // 确定计费重量
            if ($hasWeight && $hasDimensions) {
                // 既有重量又有尺寸，取较大值
                $chargeableWeight = bccomp($totalWeight, $volumetricWeight, self::SCALE) > 0
                    ? $totalWeight
                    : $volumetricWeight;
            } elseif ($hasWeight) {
                // 只有重量信息
                $chargeableWeight = $totalWeight;
            } elseif ($hasDimensions) {
                // 只有尺寸信息
                $chargeableWeight = $volumetricWeight;
            }

            $chargeableWeight = self::bcCeil($chargeableWeight);
            $weightFee = self::calculateWeightFee($chargeableWeight, $firstWeightPrice, $additionalWeightPrice);
        }

        // 确定最终计费方式
        if (!$hasWeight && !$hasDimensions) {
            // 既无重量也无尺寸，只能按件计费
            $totalFee = $pieceFee;
            $chargeBy = 'piece';
        } else {
            // 既有件数信息又有重量/尺寸信息，取较大值
            if (bccomp($pieceFee, $weightFee, self::SCALE) > 0) {
                $totalFee = $pieceFee;
                $chargeBy = 'piece';
            } else {
                $totalFee = $weightFee;
                $chargeBy = 'weight';
            }
        }

        return [
            'items_count' => count($itemsDetail),
            'items_detail' => $itemsDetail,
            'total_pieces' => (float)$totalPieces,
            'total_weight' => (float)$totalWeight,
            'total_volume' => (float)$totalVolume,
            'volumetric_weight' => (float)$volumetricWeight,
            'chargeable_weight' => (float)$chargeableWeight,
            'piece_fee' => (float)$pieceFee,
            'weight_fee' => (float)$weightFee,
            'total_fee' => (float)$totalFee,
            'charge_by' => $chargeBy,
            'has_weight' => $hasWeight,
            'has_dimensions' => $hasDimensions
        ];
    }

    /**
     * 计算按件计费的费用
     * @param string $totalPieces 总件数
     * @param string $firstPiecePrice 首件价格
     * @param string $additionalPiecePrice 续件价格
     * @return string 按件计费的总费用
     */
    private static function calculatePieceFee(string $totalPieces, string $firstPiecePrice, string $additionalPiecePrice): string
    {
        // 首件为1件，续件单位为1件
        $firstPiece = '1';
        $additionalPieceUnit = '1';

        if (bccomp($totalPieces, $firstPiece, self::SCALE) <= 0) {
            return $firstPiecePrice;
        } else {
            $additionalPieces = self::bcCeil(bcdiv(bcsub($totalPieces, $firstPiece, self::SCALE), $additionalPieceUnit, self::SCALE));
            $additionalCost = bcmul($additionalPieces, $additionalPiecePrice, self::SCALE);
            return bcadd($firstPiecePrice, $additionalCost, self::SCALE);
        }
    }

    /**
     * 计算按重量计费的费用
     * @param string $chargeableWeight 计费重量
     * @param string $firstWeightPrice 首重价格
     * @param string $additionalWeightPrice 续重价格
     * @return string 按重量计费的总费用
     */
    private static function calculateWeightFee(string $chargeableWeight, string $firstWeightPrice, string $additionalWeightPrice): string
    {
        // 首重为1kg，续重单位为1kg
        $firstWeight = '1';
        $additionalWeightUnit = '1';

        if (bccomp($chargeableWeight, $firstWeight, self::SCALE) <= 0) {
            return $firstWeightPrice;
        } else {
            $additionalUnits = self::bcCeil(bcdiv(bcsub($chargeableWeight, $firstWeight, self::SCALE), $additionalWeightUnit, self::SCALE));
            $additionalCost = bcmul($additionalUnits, $additionalWeightPrice, self::SCALE);
            return bcadd($firstWeightPrice, $additionalCost, self::SCALE);
        }
    }

    /**
     * BC Math版本的向上取整
     * @param string $number 数字
     * @return string 取整后的数字
     */
    private static function bcCeil(string $number): string
    {
        if (strpos($number, '.') !== false && bccomp(substr($number, strpos($number, '.') + 1), '0', self::SCALE) > 0) {
            return bcadd(explode('.', $number)[0], '1', 0);
        }
        return explode('.', $number)[0];
    }

    /**
     * 格式化数字为字符串
     * @param mixed $value 输入值
     * @return string|null 格式化后的字符串
     */
    private static function formatNumber($value): ?string
    {
        return $value !== null ? number_format((float)$value, self::SCALE, '.', '') : null;
    }
}
