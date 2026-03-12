export default defineAppConfig({
  pages: [
    'pages/index/index'
    // Add more pages here
  ],
  window: {
    backgroundTextStyle: 'light',
    navigationBarBackgroundColor: '#ffffff',
    navigationBarTitleText: 'MiniProgram',
    navigationBarTextStyle: 'black'
  }
  // TabBar 配置示例（需要先生成图标：node generate-icons.js）:
  // tabBar: {
  //   color: '#999999',
  //   selectedColor: '#07C160',
  //   backgroundColor: '#ffffff',
  //   list: [
  //     {
  //       pagePath: 'pages/index/index',
  //       text: '首页',
  //       iconPath: 'assets/tabbar/home.png',
  //       selectedIconPath: 'assets/tabbar/home-active.png'
  //     }
  //   ]
  // }
})

function defineAppConfig(config: any) {
  return config
}
