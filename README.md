# Preference

[![Latest Version on Packagist](https://img.shields.io/packagist/v/wsmallnews/preference.svg?style=flat-square)](https://packagist.org/packages/wsmallnews/preference)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/wsmallnews/preference/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/wsmallnews/preference/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/wsmallnews/preference/fix-php-code-style-issues.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/wsmallnews/preference/actions?query=workflow%3A"Fix+PHP+code+style+issues"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/wsmallnews/preference.svg?style=flat-square)](https://packagist.org/packages/wsmallnews/preference)

Wsmallnews Universal Preference Record System, built on Laravel + Filament. Supports unified recording and management of preference behaviors such as likes, follows, and views. Designed with polymorphic relationships, any Eloquent model can be integrated.

## Overview

- **Like**: Full-featured like/unlike/toggle like, with automatic counter maintenance
- **Follow**: Follow/unfollow/toggle follow, supports mutual follow detection, with automatic bidirectional counter maintenance
- **View**: View recording/counting, supports anonymous user statistics without authentication
- **Polymorphic Design**: Any model can be a preference subject (Preferenceable) or preference operator (Preferencer)
- **Scope Isolation**: Multi-scope data isolation via scope_type + scope_id
- **Multi-Tenancy Support**: Automatic team_id association
- **Batch Status Attachment**: Efficiently attach like/follow/view statuses to collections or paginated data (avoids N+1 queries)
- **Counter Integration**: Supports automatic increment/decrement of JSON `counter` field
- **Mutual Follow Support**: Built-in mutual follow detection and `followed_at` timestamp recording in Follow system

## Installation

You can install the package via composer:

```bash
composer require wsmallnews/preference:^1.0
```

Installing this package will publish the configuration files and migration files of both the third-party dependency package and the current package:

```bash
php artisan sn-preference:install
```

You can publish only the config file individually:

```bash
php artisan vendor:publish --tag="sn-preference-config"
```

Publish and run only the migrations individually:

```bash
php artisan vendor:publish --tag="sn-preference-migrations"
```

Multi language support, you can publish the language files using:

```bash
php artisan vendor:publish --tag="sn-preference-translations"
```

Publish the views (optional):

```bash
php artisan vendor:publish --tag="sn-preference-views"
```

This is the contents of the published config file `config/sn-preference.php`:

```php
use Wsmallnews\Preference\Models;

return [
    /**
     * Default scopeable configuration
     */
    'scopeable' => [
        'scope_type' => 'sn-preference',
        'scope_id' => 0,
    ],

    /**
     * Custom models
     */
    'models' => [
        'preference' => Models\Preference::class,
    ],

    /**
     * Base file directory, will automatically append current date (used only for filament default upload component)
     */
    'file_directory' => 'sn/preference/',
];
```

## Usage

### 1. Add Preference Capabilities to Models

Include the corresponding Traits in your models:

```php
use Illuminate\Database\Eloquent\Model;
use Wsmallnews\Preference\Models\Concerns\Preferenceable;
use Wsmallnews\Preference\Models\Concerns\Preferencer;
use Wsmallnews\Preference\Models\Concerns\Preferenceable\Likeable;
use Wsmallnews\Preference\Models\Concerns\Preferenceable\Followable;
use Wsmallnews\Preference\Models\Concerns\Preferenceable\Viewable;
use Wsmallnews\Preference\Models\Concerns\Preferencer\Liker;
use Wsmallnews\Preference\Models\Concerns\Preferencer\Follower;
use Wsmallnews\Preference\Models\Concerns\Preferencer\Viewer;

class Post extends Model
{
    use Preferenceable;   // Basic preference capability
    use Likeable;         // Can be liked
    use Followable;       // Can be followed
    use Viewable;         // Can be viewed
}

class User extends Model
{
    use Preferencer;      // Basic operator capability
    use Liker;            // Can perform like operations
    use Follower;         // Can perform follow operations
    use Viewer;           // Can perform view operations
}
```

### 2. Add counter field

Add a JSON counter field to your model's migration:

```php
$table->json('counter')->nullable()->comment('Counter: like_num, comment_num, etc.');
```

### 3. Like

```php
$post = Post::find(1);
$user = User::find(1);

// Like
$user->like($post);

// Unlike
$user->unlike($post);

// Toggle like status
$user->toggleLike($post);

// Check if liked
$user->hasLiked($post);        // true / false

// Batch attach like status
$user->attachLikeStatus($posts);

// Check if post is liked by user (from Likeable side)
$post->isLikedBy($user);       // true / false
```

The counter field is automatically maintained and updated when counts increase/decrease:

```php
counter: {"like_num": 1}
```

### 4. Follow

```php
$userA = User::find(1);
$userB = User::find(2);

// Follow
$userA->follow($userB);

// Unfollow
$userA->unfollow($userB);

// Toggle follow status
$userA->toggleFollow($userB);

// Check if following
$userA->isFollowing($userB);          // true / false

// Check if mutual follow
$userA->isMutualFollowed($userB);     // true / false

// Get following count
$userA->followingCount();             // Number of following

// Batch attach follow status
$userA->attachFollowStatus($userB);
```

Counter is automatically maintained:

```php
// Bidirectional counts automatically update after following
counter: {"follow_num": 1, "followed_num": 1}
```

When following each other, the time is automatically recorded:

```php
// When B also follows A, the followed_at timestamp is automatically written to options
options: {"followed_at": "2023-01-01 00:00:00"}
```

### 5. View

```php
$post = Post::find(1);
$user = User::find(1);

// User views post
$user->view($post);

// Anonymous view (only increments counter, no viewer recorded)
$post->view();                    // Automatically called from Viewable side

// Check if viewed
$user->hasViewed($post);          // true / false

// Check if post is viewed by user (from Viewable side)
$post->isViewedBy($user);         // true / false

// Delete view record
$user->deleteView($post);

// Batch attach view status
$user->attachViewStatus($posts);

// Clear all views of a type
$user->clearAllViews(Post::class);

// Clear views within a scope
$user->clearScopeableViews(
    ['scope_type' => 'blog', 'scope_id' => 1],
    Post::class
);
```

Counter is automatically maintained:

```php
counter: {"view_num": 1}
```

## Use Cases

| Use Case         | Traits Used               | Example                                             |
| ---------------- | ------------------------- | --------------------------------------------------- |
| Article Like     | `Likeable` + `Liker`      | User likes an article                               |
| User Follow      | `Followable` + `Follower` | Users follow each other                             |
| Content View     | `Viewable` + `Viewer`     | Record article views and user browsing history      |
| Comment Like     | `Likeable` + `Liker`      | User likes a comment (used in comment package)      |
| Product Favorite | Custom type extension     | Extend new behavior types based on Preference model |

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](.github/CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

- [smallnews](https://github.com/Wsmallnews)
- [bezhansalleh/filament-plugin-essentials](https://github.com/bezhansalleh/filament-plugin-essentials)
- [filament/filament](https://github.com/filamentphp/filament)
- [Wsmallnews/support](https://github.com/wsmallnews/support)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
