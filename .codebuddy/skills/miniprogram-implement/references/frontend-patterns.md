# Frontend Implementation Patterns (Taro MiniProgram)

## Component Mapping (CRITICAL)

### Never Use HTML Elements

```tsx
// ❌ WRONG - HTML elements
<div className="container">
  <span>Hello</span>
  <img src="logo.png" />
  <button onClick={handleClick}>Submit</button>
</div>

// ✅ CORRECT - Taro components
import { View, Text, Image, Button } from '@tarojs/components';

<View className="container">
  <Text>Hello</Text>
  <Image src="logo.png" />
  <Button onClick={handleClick}>Submit</Button>
</View>
```

### Component Mapping Table

| HTML | Taro Component | Import From |
|------|---------------|-------------|
| `<div>` | `<View>` | `@tarojs/components` |
| `<span>`, `<p>` | `<Text>` | `@tarojs/components` |
| `<img>` | `<Image>` | `@tarojs/components` |
| `<input>` | `<AtInput>` | `taro-ui` |
| `<button>` | `<AtButton>` | `taro-ui` |
| `<ul>/<li>` | `<AtList>/<AtListItem>` | `taro-ui` |

**Use Taro native components for**: View, Text, Image, ScrollView, Swiper, Button (layout and basic elements)

## Taro UI Configuration (CRITICAL)

### Style Import

```tsx
// app.tsx - Global style import (already done in template)
import 'taro-ui/dist/style/index.scss';

// ❌ WRONG - Do NOT import Taro UI styles in page files
// import 'taro-ui/dist/style/components/button.scss';
```

### H5 Configuration

Already configured in `config/index.ts`:

```ts
h5: {
  esnextModules: ['taro-ui']  // Required for H5
}
```

## Taro UI Component Usage (CRITICAL)

### AtInput - onChange Receives Value Directly

All Taro UI form components (AtInput, AtTextarea, AtSearchBar, etc.) onChange returns value directly, NOT event object:

```tsx
// ✅ Taro UI form components - onChange receives value directly
import { AtInput, AtTextarea, AtSearchBar } from 'taro-ui';
<AtInput value={val} onChange={(value) => setVal(value)} />
<AtTextarea value={val} onChange={(value) => setVal(value)} />
<AtSearchBar value={val} onChange={(value) => setVal(value)} />

// ✅ Taro native components - onInput receives event
import { Input, Textarea } from '@tarojs/components';
<Input value={val} onInput={(e) => setVal(e.detail.value)} />
<Textarea value={val} onInput={(e) => setVal(e.detail.value)} />

// ❌ WRONG - causes "Cannot read properties of undefined (reading 'value')"
<AtInput onChange={(e) => setVal(e.detail.value)} />
<AtTextarea onChange={(e) => setVal(e.detail.value)} />
```

### AtButton

```tsx
import { AtButton } from 'taro-ui';

<AtButton type="primary" onClick={handleClick}>确定</AtButton>
<AtButton type="secondary">取消</AtButton>
<AtButton type="primary" loading>加载中</AtButton>
<AtButton type="primary" disabled>禁用</AtButton>
```

### AtModal

```tsx
import { AtModal, AtModalHeader, AtModalContent, AtModalAction } from 'taro-ui';
import { Button } from '@tarojs/components';

// Simple usage
<AtModal
  isOpened={isOpen}
  title="提示"
  content="确认删除吗？"
  confirmText="确认"
  cancelText="取消"
  onConfirm={handleConfirm}
  onCancel={handleCancel}
  onClose={() => setIsOpen(false)}
/>

// Custom content
<AtModal isOpened={isOpen} onClose={() => setIsOpen(false)}>
  <AtModalHeader>标题</AtModalHeader>
  <AtModalContent>内容区域</AtModalContent>
  <AtModalAction>
    <Button onClick={handleCancel}>取消</Button>
    <Button onClick={handleConfirm}>确定</Button>
  </AtModalAction>
</AtModal>
```

### Modal/Dialog Usage

- **Simple confirm dialog**: Use `Taro.showModal({ title, content, confirmText, cancelText })`
- **Modal with custom content/form**: Use `<AtModal>` with `<AtModalHeader>`, `<AtModalContent>`, `<AtModalAction>`

### AtModal + AtFloatLayout Z-Index Conflict (CRITICAL)

- ❌ **WRONG**: Triggering AtFloatLayout from inside AtModal causes the float layer to be hidden behind the modal
- ✅ **CORRECT**: Two solutions:
  1. **View switching mode**: Use state to switch between different views within the same AtModal instead of opening a new float layer
  2. **Close then open**: Close AtModal first, then open AtFloatLayout, reopen AtModal after selection is complete

```tsx
// ✅ Recommended: View switching mode
<AtModal isOpened={showModal}>
  <AtModalHeader>{showPicker ? 'Select Category' : 'Form Title'}</AtModalHeader>
  <AtModalContent>
    {showPicker ? (
      <View className="picker-grid">...</View>
    ) : (
      <View className="form-content">...</View>
    )}
  </AtModalContent>
</AtModal>

// ❌ Avoid: Triggering FloatLayout from inside Modal
<AtModal isOpened={showModal}>
  <View onClick={() => setShowFloat(true)}>Select</View>
</AtModal>
<AtFloatLayout isOpened={showFloat}>...</AtFloatLayout>
```

## Navigation (Taro APIs, NOT React Router)

### Navigation Methods

```typescript
import Taro from '@tarojs/taro';

// Navigate to page (push)
Taro.navigateTo({ url: '/pages/detail/index?id=123' });

// Switch to tab page
Taro.switchTab({ url: '/pages/index/index' });

// Go back
Taro.navigateBack({ delta: 1 });

// Redirect (replace)
Taro.redirectTo({ url: '/pages/login/index' });

// Relaunch (clear stack)
Taro.reLaunch({ url: '/pages/index/index' });
```

### Getting URL Parameters

```typescript
// In page component
const instance = Taro.getCurrentInstance();
const { id, type } = instance.router?.params || {};
```

### NOT Compatible (DO NOT USE)

- ❌ `react-router-dom` - Not supported in MiniProgram
- ❌ `useNavigate`, `useLocation` - React Router hooks
- ❌ `<Link>`, `<Routes>`, `<Route>` - React Router components

## Storage (Taro APIs, NOT localStorage)

```typescript
import Taro from '@tarojs/taro';

// Sync methods
Taro.setStorageSync('token', 'abc123');
const token = Taro.getStorageSync('token');
Taro.removeStorageSync('token');

// Async methods
await Taro.setStorage({ key: 'user', data: userObject });
const { data } = await Taro.getStorage({ key: 'user' });
await Taro.removeStorage({ key: 'user' });

// ❌ WRONG - localStorage not available in MiniProgram
localStorage.setItem('token', 'abc123');
```

## Page Lifecycle

```tsx
import { useDidShow, useDidHide } from '@tarojs/taro';

function MyPage() {
  // componentDidMount equivalent
  useEffect(() => {
    console.log('Page mounted');
    return () => console.log('Page unmounted');
  }, []);

  // Page shows (every time, including back navigation)
  useDidShow(() => {
    console.log('Page shown');
  });

  // Page hides
  useDidHide(() => {
    console.log('Page hidden');
  });
}
```

## Loading & Feedback

```typescript
import Taro from '@tarojs/taro';

// Modal loading
Taro.showLoading({ title: '加载中...' });
Taro.hideLoading();

// Toast
Taro.showToast({ title: '成功', icon: 'success' });

// Dialog
Taro.showModal({ title: '提示', content: '内容' });
```

**Taro UI Components for Feedback:**
- `<AtActivityIndicator>` - Loading indicator
- `<AtMessage>` - Message notification
- `<AtToast>` - Toast notification

## Styling

### Unit System (CRITICAL - 750px Design Width)

Taro uses **750px design width**. Write styles in `px` based on 750px design, Taro auto-converts:
- **MiniProgram**: px → rpx (1:1)
- **H5**: px → rem (responsive)

```scss
// Write in px based on 750px design width
// Font sizes, paddings, margins all use px
.button {
  width: 200px;      // 200px on 750px design = 100px on 375px device
  height: 88px;      // Minimum touch target (88px = 44px physical)
  font-size: 28px;   // Standard body text (28px = 14px physical)
  padding: 24px 32px;
}

// Common sizes in 750px design:
// - Body text: 28px (= 14px physical)
// - Small text: 24px (= 12px physical)
// - Title text: 32-36px (= 16-18px physical)
// - Large title: 40-48px (= 20-24px physical)
// - Button height: 88px (= 44px physical)
// - Icon size: 40-48px (= 20-24px physical)
// - Standard padding: 24px-32px (= 12-16px physical)
```

### ⚠️ Size Reference Table (750px Design → Physical)

| Purpose | 750px Design | Physical (375px) |
|---------|-------------|------------------|
| Body text | 28px | 14px |
| Small text | 24px | 12px |
| Subtitle | 32px | 16px |
| Title | 36px | 18px |
| Large title | 48px | 24px |
| Button height | 88px | 44px |
| Touch target min | 88px | 44px |
| Standard padding | 32px | 16px |
| Small padding | 24px | 12px |
| Icon (small) | 32px | 16px |
| Icon (medium) | 40px | 20px |
| Icon (large) | 48px | 24px |

### Page Padding

```scss
// Always add horizontal padding to pages
.page-container {
  padding: 24px 32px;  // Ensure content doesn't touch screen edges
}
```

### ScrollView Padding Issue (CRITICAL)

ScrollView component does NOT apply padding correctly in H5/MiniProgram. **Always wrap content inside ScrollView with a View container**:

```tsx
// ❌ WRONG - padding on ScrollView won't work properly
<ScrollView className="scroll-container" scrollY>
  <View className="card">Content</View>
</ScrollView>
// .scroll-container { padding: 24px 32px; } // This padding may NOT work!

// ✅ CORRECT - wrap content in a View with padding
<ScrollView className="scroll-container" scrollY>
  <View className="scroll-content">
    <View className="card">Content</View>
  </View>
</ScrollView>
// .scroll-container { flex: 1; }
// .scroll-content { padding: 24px 32px; }
```

### Card/List Items

Ensure proper margins to avoid content touching edges:

```scss
.card-item {
  margin: 0 32px 24px;  // Horizontal margin for cards
}
```

### Grid Layout (Recommended)

```scss
// ✅ CORRECT - Grid for equal-width items
.grid-container {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 16px;
}

// ❌ AVOID - Flex with percentage widths
.flex-container {
  display: flex;
  flex-wrap: wrap;
  .item { width: calc(25% - 15px); }  // May not fill properly
}
```

### Flex Layout

```scss
// Use for single-row layouts
.flex-row {
  display: flex;
  justify-content: space-between;  // Distribute items
  align-items: center;
}
```

### Fixed Bottom with TabBar (CRITICAL)

H5 TabBar height is ~100px. **SCSS conditional compilation does NOT work**, always set `bottom: 100px` directly:

```scss
.fixed-bottom-btn {
  position: fixed;
  left: 0;
  right: 0;
  bottom: 100px;  // Always add 100px offset for TabBar (works for both H5 and MiniProgram)
  padding: 24px 32px;
  background: #fff;
}
```

- **CRITICAL**: Do NOT use `/* #ifdef H5 */` syntax - it does NOT work in Taro SCSS files
- Always set `bottom: 100px` directly to avoid TabBar overlap

### AtTag Styling Fix

```scss
// AtTag has display issues in H5 mode
.tag-wrapper {
  .at-tag {
    padding: 16px 32px !important;
    font-size: 28px !important;
    border-radius: 40px !important;
    line-height: 1.4 !important;
    height: auto !important;
    white-space: nowrap !important;
  }
}
```

### Custom Component Colors

```tsx
// Taro UI default colors may not match app theme
<AtButton 
  type="primary" 
  customStyle={{ backgroundColor: '#07C160' }}
>
  确定
</AtButton>
```

## Design Guidelines

### Color Scheme

- Primary action: `#07C160` (WeChat Green)
- Text primary: `#333333`
- Text secondary: `#666666`
- Text hint: `#CCCCCC`
- Background page: `#F7F7F7`
- Background cards: `#FFFFFF`

### Touch Targets

- Minimum size: 88rpx (44px)
- Spacing between targets: 16rpx minimum

### Layout

- Design width: 750px
- Spacing scale: 8/16/24/32rpx
- Card-based layout
- Vertical scroll

### Performance

- Bundle size: < 2MB for MiniProgram
- Lazy load images
- Use skeleton screens for loading states
- Minimize re-renders

## Pre-Completion Verification

Before marking any file complete:

1. **⚠️ NO process.env**: Causes "ReferenceError: process is not defined". Use `Taro.getEnv()` instead
2. **Components**: All using Taro components, no HTML elements
3. **Navigation**: Using Taro APIs, no React Router
4. **Storage**: Using Taro storage, no localStorage
5. **AtInput**: onChange receives value, not event
6. **Imports**: Every component has correct import source
7. **Styling**: Page has proper padding, touch targets meet minimum
8. **ScrollView**: Content wrapped in View container for padding
9. **Fixed Bottom**: Using `bottom: 100px` directly, no conditional compilation
10. **API calls**: Use existing `src/services/api-client.ts`, do NOT create new api.ts
