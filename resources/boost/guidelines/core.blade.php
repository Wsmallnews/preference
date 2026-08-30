## Preference 包（wsmallnews/preference）

`wsmallnews/preference` 是一个多态偏好/互动追踪系统，提供点赞（like）、关注（follow）、浏览（view）三种互动类型的完整功能。命名空间根为 `Wsmallnews\Preference`，Blade 视图前缀为 `sn-preference`，配置文件为 `config/sn-preference.php`。

### 核心架构

通过单张 `sn_preferences` 表 + 多态关联实现三种互动类型：

- **preferencer（操作者）**：执行点赞/关注/浏览的用户或模型，必须实现 `HasSnIdentifiable` 接口
- **preferenceable（目标）**：被操作的内容实体，必须实现 `HasSnSubject` 接口
- **计数器**：所有操作自动维护 JSON 计数器（`counter->like_num`、`counter->follow_num`、`counter->followed_num`、`counter->view_num`），配合 support 包的 `CounterCast` 使用

### 依赖的接口（来自 support 包）

preference 包的 Blade 组件依赖以下两个接口获取展示数据：

- `Wsmallnews\Support\Contracts\HasSnIdentifiable` — 操作者侧接口（`getSnId()`、`getSnName()`、`getSnAvatarUrl()`、`getSnEmail()`）
- `Wsmallnews\Support\Contracts\HasSnSubject` — 目标侧接口（`getSnSubjectId()`、`getSnSubjectTitle()`、`getSnSubjectDescription()`、`getSnSubjectCoverUrl()`）

User 模型可直接 use `Wsmallnews\Support\Concerns\UserIdentifiable` trait 来实现 `HasSnIdentifiable`。`HasSnSubject` 没有默认 trait，每个模型需自行实现。

### hasLink 机制

三个基础 Blade 组件（`sn-preference::components.preference`、`preferenceable`、`preferencer`）均接受 `hasLink` prop（默认 `false`）。**注意：`isLink` 已改名为 `hasLink`，旧属性名不再有效。**

三个组件均接受 `href` prop（`string|Closure|null`）用于传入跳转链接（接口契约不含链接方法，链接由调用方传入；`preference` 组件的闭包优先接收 preferenceable，缺失时接收 preferencer）：

- **`hasLink=true` 且 `href` 解析出非空 URL**：组件渲染为 `<a>` 标签，可点击跳转
- **`hasLink=true` 但 URL 为空**：组件渲染为 `<div>`，点击时通过 `wire:click.stop` 分发 Livewire 事件（`sn-preference-preferencer-click`、`sn-preference-preferenceable-click` 或 `sn-preference-preference-click`），由父组件处理
- **panel 语境（后台渲染）且未传 `href`**：组件自动兜底 `FilamentModelHelper::getUrl()`（后台资源链接），配合 `hasLink=true` 渲染为 `<a>`（panel 组件视图已默认 `has-link`）
- **`hasLink=false`（默认）**：组件渲染为普通 `<div>`，无交互

前端 Livewire 组件（`sn-preference-components-follows/likes/views`）支持 `hrefRoute` prop（路由名字符串，如 `sn-cms.posts.show`），传入后行项以 `sn_route($hrefRoute, $record)` 生成跳转链接（Livewire 无法传闭包，故用路由名）。

@verbatim
```blade
{{-- 可点击跳转的偏好列表项（调用方直传链接） --}}
<x-sn-preference::preferenceable
    :preference="$item"
    :preferenceable="$item->preferenceable"
    :has-link="true"
    :href="fn ($record) => route('posts.show', $record)"
/>

{{-- 无链接，点击时分发事件 --}}
<x-sn-preference::preferencer
    :preference="$item"
    :preferencer="$item->preferencer"
    :has-link="true"
/>
```
@endverbatim

### Model traits（操作者侧 — Preferencer）

模型必须实现 `HasSnIdentifiable` 接口后才能使用以下 traits。所有操作自动维护 JSON 计数器。

#### Follower（关注）

`Wsmallnews\Preference\Models\Concerns\Preferencer\Follower`：

@verbatim
```php
use Wsmallnews\Preference\Models\Concerns\Preferencer\Follower;

// 核心操作
$user->follow($post);           // 关注，返回 Preference 记录
$user->unfollow($post);         // 取消关注，返回 bool
$user->toggleFollow($post);     // 切换关注状态

// 状态查询
$user->isFollowing($post);          // 是否已关注
$user->isMutualFollowed($post);     // 是否互相关注

// 关联和统计
$user->followingUsers();        // MorphToMany，我关注的用户列表
$user->followingCount();        // 我关注的用户数量
$user->follows();               // MorphMany 关联（type='follow'）

// 批量附加关注状态
$user->attachFollowStatus($posts);  // 为集合中的每个模型设置 has_followed 属性
```
@endverbatim

互相关注时，系统会在 `options` JSON 列中自动写入 `followed_at` 时间戳。

#### Liker（点赞）

`Wsmallnews\Preference\Models\Concerns\Preferencer\Liker`：

@verbatim
```php
use Wsmallnews\Preference\Models\Concerns\Preferencer\Liker;

// 核心操作
$user->like($post);             // 点赞，返回 Preference 记录
$user->unlike($post);           // 取消点赞，返回 bool
$user->toggleLike($post);       // 切换点赞状态

// 状态查询
$user->hasLiked($post);         // 是否已点赞

// 关联
$user->likes();                 // MorphMany 关联（type='like'）

// 批量附加点赞状态
$user->attachLikeStatus($posts);    // 为集合中的每个模型设置 has_liked 属性
```
@endverbatim

#### Viewer（浏览）

`Wsmallnews\Preference\Models\Concerns\Preferencer\Viewer`：

@verbatim
```php
use Wsmallnews\Preference\Models\Concerns\Preferencer\Viewer;

// 核心操作
$user->view($post);             // 记录浏览，返回 Preference 记录（重复浏览时更新 updated_at）

// 状态查询
$user->hasViewed($post);        // 是否已浏览

// 删除记录
$user->deleteView($post);       // 删除单条浏览记录
$user->clearAllViews($type);    // 清空所有浏览记录（不限租户和 scope）
$user->clearScopeableViews(['scope_type' => 'post', 'scope_id' => 0], $type);  // 按 scope 清空

// 关联
$user->views();                 // MorphMany 关联（type='view'）

// 批量附加浏览状态
$user->attachViewStatus($posts);    // 为集合中的每个模型设置 has_viewed 属性
```
@endverbatim

### Model traits（目标侧 — Preferenceable）

被操作的内容模型使用以下 traits。

#### Followable（被关注）

`Wsmallnews\Preference\Models\Concerns\Preferenceable\Followable`：

@verbatim
```php
use Wsmallnews\Preference\Models\Concerns\Preferenceable\Followable;

// 状态查询
$post->isFollowedBy($user);         // 是否被某用户关注
$post->isMutualFollowedWith($user); // 是否与某用户互关

// 关联和统计
$post->userFollowers();             // MorphToMany，粉丝列表（仅 User 类型）
$post->followersCount();            // 粉丝数量
$post->follows();                   // MorphMany 关联（type='follow'）
```
@endverbatim

#### Likeable（被点赞）

`Wsmallnews\Preference\Models\Concerns\Preferenceable\Likeable`：

@verbatim
```php
use Wsmallnews\Preference\Models\Concerns\Preferenceable\Likeable;

// 状态查询
$post->isLikedBy($user);        // 是否被某用户点赞

// 关联
$post->userLikers();            // MorphToMany，点赞用户列表（仅 User 类型）
$post->likes();                 // MorphMany 关联（type='like'）
```
@endverbatim

#### Viewable（被浏览）

`Wsmallnews\Preference\Models\Concerns\Preferenceable\Viewable`：

@verbatim
```php
use Wsmallnews\Preference\Models\Concerns\Preferenceable\Viewable;

$post->view($user);             // 记录浏览（$user 为 null 时只增加计数不记录）
$post->isViewedBy($user);       // 是否被某用户浏览过
$post->userViewers();           // MorphToMany，浏览用户列表（仅 User 类型）
$post->views();                 // MorphMany 关联（type='view'）
```
@endverbatim

### Preference 模型

`Wsmallnews\Preference\Models\Preference`（可通过 `config('sn-preference.models.preference')` 替换）。

表 `sn_preferences` 关键字段：

| 字段 | 类型 | 说明 |
|---|---|---|
| `id` | bigint | 主键 |
| `team_id` | bigint, nullable | 多租户 |
| `scope_type` | string, nullable | 作用域类型 |
| `scope_id` | bigint, default 0 | 作用域 ID（0 = 全局） |
| `type` | string | 偏好类型：`'follow'`、`'like'`、`'view'` |
| `preferencer_type` | string | 操作者多态类型 |
| `preferencer_id` | bigint | 操作者多态 ID |
| `preferenceable_type` | string | 目标多态类型 |
| `preferenceable_id` | bigint | 目标多态 ID |
| `options` | json | 额外数据（互关时存 `followed_at` 等） |
| `created_at` / `updated_at` / `deleted_at` | timestamps | 软删除 |

**查询作用域：**

@verbatim
```php
Preference::withType('follow')              // 按 type 筛选
    ->withPreferencer($user)                // 按操作者筛选
    ->withPreferenceable($post)             // 按目标筛选
    ->withPreferenceType($postOrClass);     // 按目标类型筛选
```
@endverbatim

### Livewire 组件

#### 前端组件（带管理功能）

三个组件均继承 `Wsmallnews\Preference\Livewire\Components\Base`（→ `Wsmallnews\Support\Livewire\Base`，使用 `Scopeable` trait）：

| 组件 | 注册名 | 功能 |
|---|---|---|
| `Livewire\Components\Follows` | `sn-preference-components-follows` | 关注列表，支持取消关注和批量操作 |
| `Livewire\Components\Likes` | `sn-preference-components-likes` | 点赞列表，支持取消点赞和批量操作 |
| `Livewire\Components\Views` | `sn-preference-components-views` | 浏览列表，支持删除和批量删除 |

**通用属性和特性：**

- `$preferencer` / `$preferenceable` / `$listType`（`'preferencer'`、`'preferenceable'`、默认全部）
- 均使用 `CanPagination`（**已包含 `WithPagination`，不要重复 use**）、`HasAuth`、`HasProperties`、`CanBeContained`、`CanManage`
- 管理模式下支持单选/全选和批量操作

**使用示例：**

@verbatim
```blade
{{-- 显示某用户的所有关注 --}}
<livewire:sn-preference-components-follows
    :preferencer="$user"
    list-type="preferencer"
    :can-manage="true"
/>

{{-- 显示某文章的所有点赞用户 --}}
<livewire:sn-preference-components-likes
    :preferenceable="$post"
    list-type="preferenceable"
/>
```
@endverbatim

#### Filament 面板组件（只读）

| 组件 | 注册名 |
|---|---|
| `Filament\Pages\Preference\Components\Follows` | `sn-preference-fi-follows` |
| `Filament\Pages\Preference\Components\Likes` | `sn-preference-fi-likes` |
| `Filament\Pages\Preference\Components\Views` | `sn-preference-fi-views` |

这三个组件无管理功能，用于 Filament 面板页面中嵌入展示。

#### Filament Widget 包装器

`Wsmallnews\Preference\Filament\Pages\Preference\Widgets\` 下的 `Follows`、`Likes`、`Views`，接受 `$record` 和 `$widgetType`（`'preferenceable'` 或 `'preferencer'`），内部渲染对应的 Filament 面板组件。

@verbatim
```blade
{{-- 在 Filament 页面中使用 --}}
<x-filament-widgets::widgets>
    @foreach ($widgets as $widget)
        {{ $this->makeFilamentWidget($widget) }}
    @endforeach
</x-filament-widgets::widgets>
```
@endverbatim

### 配置

`config/sn-preference.php`：

```php
return [
    'scopeable' => [
        'scope_type' => 'sn-preference',  // 默认作用域类型
        'scope_id' => 0,                  // 0 = 全局
    ],
    'models' => [
        'preference' => Models\Preference::class,  // 可替换模型
    ],
    'file_directory' => 'sn/preference/',  // 文件存储目录
];
```

### Utils 工具类

`Wsmallnews\Preference\Support\Utils` — 全部为静态方法：

| 方法 | 说明 |
|---|---|
| `getConfig(?string $name, $default)` | 读取 `sn-preference` 配置（dot notation） |
| `getScopeableContext()` | 从配置创建 ScopeableContext 值对象 |
| `getScopeable()` | 返回 `['scope_type' => '...', 'scope_id' => 0]` |
| `getScopeType()` | 获取默认 scope_type |
| `getScopeId()` | 获取默认 scope_id |
| `getModel(string $name, bool $shouldException = true)` | 获取配置的模型类名，`false` 时不抛异常 |
| `getPreferenceModel()` | `getModel('preference')` 快捷方式 |
| `getFileDirectory(?string $type)` | 获取文件目录（自动追加日期），如 `sn/preference/image/20260527` |

### 正确命名空间速查

| 类别 | 命名空间 |
|---|---|
| Preference 模型 | `Wsmallnews\Preference\Models\Preference` |
| 操作者侧 traits | `Wsmallnews\Preference\Models\Concerns\Preferencer\` |
| 目标侧 traits | `Wsmallnews\Preference\Models\Concerns\Preferenceable\` |
| Livewire 组件 | `Wsmallnews\Preference\Livewire\Components\` |
| Livewire Base | `Wsmallnews\Preference\Livewire\Components\Base` |
| Livewire Traits | `Wsmallnews\Preference\Livewire\Concerns\` |
| Filament 页面组件 | `Wsmallnews\Preference\Filament\Pages\Preference\Components\` |
| Filament 挂件 | `Wsmallnews\Preference\Filament\Pages\Preference\Widgets\` |
| Utils | `Wsmallnews\Preference\Support\Utils` |
| Facade | `Wsmallnews\Preference\Facades\Preference` |
| 异常 | `Wsmallnews\Preference\Exceptions\` |

### 常见错误

- **preferencer 模型必须实现 `HasSnIdentifiable` 接口**，否则 Blade 组件渲染会失败。User 模型可直接 use `UserIdentifiable` trait。
- **preferenceable 模型必须实现 `HasSnSubject` 接口**，否则 Blade 组件渲染会失败。两个接口均不含跳转链接方法，只有固有展示数据。
- **跳转链接由调用方传入**：三个 Blade 组件均通过 `href` prop（string|Closure）接收链接；未传时点击会分发 Livewire 事件，由父组件监听跳转。panel 侧由 `FilamentModelHelper::getUrl()` 统一走 `resolveResourceUrl()` 兜底。
- **`isLink` 已改名为 `hasLink`**，旧属性名不再有效，使用 `isLink` 的代码需更新。
- **`CanPagination` 已包含 `WithPagination`**，不要在 Livewire 组件中重复 `use WithPagination`。
- **counter 字段使用 JSON 格式**，模型中需配合 support 包的 `CounterCast` 使用：`'counter' => CounterCast::class`。
- **`scope_id = 0` 表示全局作用域**，不要用 `where('scope_id', 0)` 直接查询——使用模型 trait 提供的 `scopeScopeId(0)`，它内部使用 `whereIn`。
- **preferenceable 模型必须 use 对应的 Preferenceable trait**（如 `Followable`），否则 preferencer 侧操作会抛出 `InvalidArgumentException`。
- **`Follower::follow()` 不允许关注自己**，会抛出 `InvalidArgumentException('Cannot follow yourself.')`。
- **`Utils` 所有方法都是静态的**，使用 `Utils::getConfig()` 而非 `(new Utils)->getConfig()`。
- **`Utils::getModel()` 默认会抛异常**，传递 `false` 作为第二个参数以允许返回 `null`。
