<?php

// translations for Wsmallnews/Preference
return [
    'components' => [
        // Views (浏览记录)
        'view' => 'View',
        'views_title' => 'Viewers',
        'views_title_user' => 'Browsing History',
        'views_title_subject' => 'Viewers',
        'views_empty_heading_user' => 'No browsing history',
        'views_empty_description_user' => 'This user has not browsed any content yet.',
        'views_empty_heading_subject' => 'No viewers yet',
        'views_empty_description_subject' => 'No one has viewed this content yet.',

        // Likes (喜欢记录)
        'like' => 'Like',
        'likes_title' => 'Likes',
        'likes_title_user' => 'Liked Items',
        'likes_title_subject' => 'Liked By',
        'likes_empty_heading_user' => 'No liked items',
        'likes_empty_description_user' => 'This user has not liked any content yet.',
        'likes_empty_heading_subject' => 'No likes yet',
        'likes_empty_description_subject' => 'No one has liked this content yet.',

        // Follows (关注列表)
        'follow' => 'Follow',
        'follows_title' => 'Following Records',
        'follows_title_user' => 'Following',
        'follows_title_subject' => 'Followers',
        'follows_empty_heading_user' => 'Not following anyone',
        'follows_empty_description_user' => 'This user is not following anyone yet.',
        'follows_empty_heading_subject' => 'No followers yet',
        'follows_empty_description_subject' => 'No one is following this account yet.',
    ],

    'widget' => [
        'followed_at' => 'Followed :time',
        'liked_at' => 'Liked :time',
        'viewed_at' => 'Viewed :time',
        'mutual_follow' => 'Mutual',
    ],

    'action' => [
        'manage' => 'Manage',
        'done' => 'Done',
        'select_all' => 'Select All',
        'deselect_all' => 'Deselect All',
        'selected_count' => ':count selected',
        'delete_view' => 'Delete',
        'delete_view_heading' => 'Are you sure you want to delete this view record?',
        'delete_view_description' => 'This action cannot be undone.',
        'delete_view_success' => 'View record deleted.',
        'delete_selected' => 'Delete (:count)',
        'batch_delete_view_heading' => 'Are you sure you want to delete :count selected view records?',
        'batch_delete_view_success' => ':count view records deleted.',
        'unfollow' => 'Unfollow',
        'unfollow_heading' => 'Are you sure you want to unfollow?',
        'unfollow_success' => 'Unfollowed successfully.',
        'unfollow_selected' => 'Unfollow (:count)',
        'batch_unfollow_heading' => 'Are you sure you want to unfollow :count selected users?',
        'batch_unfollow_success' => ':count users unfollowed.',
        'unlike' => 'Unlike',
        'unlike_heading' => 'Are you sure you want to unlike?',
        'unlike_success' => 'Unliked successfully.',
        'unlike_selected' => 'Unlike (:count)',
        'batch_unlike_heading' => 'Are you sure you want to unlike :count selected items?',
        'batch_unlike_success' => ':count items unliked.',
    ],
];
