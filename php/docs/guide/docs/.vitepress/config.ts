import { defineConfig } from 'vitepress'

// https://vitepress.vuejs.org/config/app-configs
export default defineConfig({
    lang: 'zh-CN',
    title: 'NexoPHP',
    description: 'NexoPHP 是一个基于 PHP 8.x 版本的框架，它的目标是为 PHP 开发者提供一个简单、快速、安全的软件开发框架',

    /**
     * 菜单 
     */
    themeConfig: {
        nav: [
            { text: '首页', link: '/' },
            { text: '入门', link: '/home' },
            { text: '进阶', link: '/guide' },
            { text: '数据库', link: '/db' }, 
            { text: '函数', link: '/fun' }, 
            { text: 'Action', link: '/action' }, 
            { text: '模块', link: '/module' }, 
        ],

        // 侧边栏配置 - 自动生成
        sidebar: 'auto',

        // 大纲配置 - 显示页面内的标题层级
        outline: {
            level: [1, 2], // 显示 h1 和 h2 标题，h2下的子标题通过折叠功能控制
            label: ' '
        },

        // 启用自动生成侧边栏
        sidebarMenuCollapsed: true,
        
        // 文档页脚
        docFooter: {
            prev: '上一页',
            next: '下一页'
        },
        
        // 编辑链接
        editLink: {
            pattern: 'https://github.com/nexophp/nexophp',
            text: '在 GitHub 上编辑此页面'
        },
        
        // 最后更新时间
        lastUpdated: {
            text: '最后更新于',
            formatOptions: {
                dateStyle: 'short',
                timeStyle: 'medium'
            }
        },

        // 添加搜索功能
        search: {
            provider: 'local',
            options: {
                locales: {
                    root: {
                        translations: {
                            button: {
                                buttonText: '搜索文档',
                                buttonAriaLabel: '搜索文档'
                            },
                            modal: {
                                noResultsText: '无法找到相关结果',
                                resetButtonTitle: '清除查询条件',
                                footer: {
                                    selectText: '选择',
                                    navigateText: '切换',
                                    closeText: '关闭'
                                }
                            }
                        }
                    }
                }
            }
        }
    },
})