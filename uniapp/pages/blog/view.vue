<template>
	<view>
		
		<view class="content" style="padding: 10px;">
			<mp-html :content="content.content || ''" :lazy-load="true" :selectable="true"></mp-html>

		</view>
	</view>
</template>

<script>
import mpHtml from '@/uni_modules/mp-html/components/mp-html/mp-html.vue'
export default {
	components: {
		mpHtml,
	},
	data() {
		return {
			title: '',
			content: {}
		}
	},
	onLoad(options) {
		this.title = options.title;
		this.load();
	},
	methods: {
		load() {
			this.ajax(this.config.blog.view, { title: this.title }).then(res => {
				this.content = res.data;
				this.set_title(this.content.title || '');
			})
		}
	}
}
</script>

<style></style>
