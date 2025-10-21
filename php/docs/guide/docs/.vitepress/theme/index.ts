// .vitepress/theme/index.ts
import DefaultTheme from 'vitepress/theme-without-fonts'
import './custom.css'

export default {
  ...DefaultTheme,
  enhanceApp({ app, router, siteData }) {
    if (typeof window !== 'undefined') {
      // 页面路由变化时重新初始化
      router.onAfterRouteChanged = () => {
        setTimeout(() => {
          addOutlineCollapse()
        }, 300)
      }
      
      // 页面首次加载时初始化
      setTimeout(() => {
        addOutlineCollapse()
      }, 800)
    }
  }
}

function addOutlineCollapse() {
  const outlineContainer = document.querySelector('.VPDocAsideOutline, .VPDocOutline, .VPDocOutlineItem')
  if (!outlineContainer) {
    return
  }
  
  // 查找所有大纲链接
  const outlineLinks = outlineContainer.querySelectorAll('.outline-link')
  
  outlineLinks.forEach((link) => {
    const li = link.closest('li')
    if (!li) return
    
    // 检查是否已经处理过
    if (li.hasAttribute('data-collapse-processed')) {
      return
    }
    
    // 查找嵌套的ul元素
    const nestedUl = li.querySelector('ul')
    
    if (nestedUl) {
      // 标记为已处理
      li.setAttribute('data-collapse-processed', 'true')
      
      // 添加折叠类
      li.classList.add('has-children')
      
      // 创建折叠按钮
      const collapseBtn = document.createElement('span')
      collapseBtn.className = 'collapse-btn'
      collapseBtn.innerHTML = '▼'
      
      // 设置link为相对定位并添加按钮
      link.style.position = 'relative'
      link.style.paddingRight = '20px'
      link.appendChild(collapseBtn)
      
      // 添加点击事件
      collapseBtn.addEventListener('click', (e) => {
        e.preventDefault()
        e.stopPropagation()
        
        console.log('Collapse button clicked, current classes:', li.className)
        
        // 切换折叠状态
        if (li.classList.contains('collapsed')) {
          li.classList.remove('collapsed')
          li.classList.add('expanded')
          console.log('Expanding to:', li.className)
        } else {
          li.classList.remove('expanded')
          li.classList.add('collapsed')
          console.log('Collapsing to:', li.className)
        }
      })
      
      // 默认设置为折叠状态
      li.classList.add('collapsed')
    }
  })
}