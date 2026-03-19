import { Component } from 'react';
import { View, Text } from '@tarojs/components';
import { AtActivityIndicator } from 'taro-ui';
import './index.scss';

class Index extends Component {
  render() {
    return (
      <View className="index-container">
        <Text className="title">GenieKit</Text>
        <Text className="subtitle">AI 驱动的智能开发工具</Text>
        <AtActivityIndicator mode="center" color="#07C160" content="正在构建应用..." />
      </View>
    );
  }
}

export default Index;
