<?php

// translations for Wsmallnews/Preference
return [
    'components' => [
        // Views (浏览记录)
        'view' => '浏览',
        'views_title' => '浏览记录',
        'views_title_user' => '浏览历史',
        'views_title_subject' => '浏览记录',
        'views_empty_heading_user' => '暂无浏览历史',
        'views_empty_description_user' => '该用户还没有浏览过任何内容。',
        'views_empty_heading_subject' => '暂无浏览者',
        'views_empty_description_subject' => '还没有人浏览过此内容。',

        // Likes (喜欢记录)
        'like' => '喜欢',
        'likes_title' => '喜欢记录',
        'likes_title_user' => '喜欢的内容',
        'likes_title_subject' => '被喜欢',
        'likes_empty_heading_user' => '暂无喜欢的内容',
        'likes_empty_description_user' => '该用户还没有喜欢过任何内容。',
        'likes_empty_heading_subject' => '暂无喜欢',
        'likes_empty_description_subject' => '还没有人喜欢过此内容。',

        // Follows (关注列表)
        'follow' => '关注',
        'follows_title' => '关注记录',
        'follows_title_user' => '已关注',
        'follows_title_subject' => '粉丝',
        'follows_empty_heading_user' => '暂未关注任何人',
        'follows_empty_description_user' => '该用户还没有关注任何人。',
        'follows_empty_heading_subject' => '暂无粉丝',
        'follows_empty_description_subject' => '还没有人关注此账号。',
    ],

    'widget' => [
        'followed_at' => ':time 关注',
        'liked_at' => ':time 喜欢',
        'viewed_at' => ':time 浏览',
        'mutual_follow' => '互相关注',
    ],

    'action' => [
        'manage' => '管理',
        'done' => '完成',
        'select_all' => '全选',
        'deselect_all' => '取消全选',
        'selected_count' => '已选 :count 项',
        'delete_view' => '删除',
        'delete_view_heading' => '确定要删除此浏览记录吗？',
        'delete_view_description' => '删除后将无法恢复。',
        'delete_view_success' => '已删除浏览记录。',
        'delete_selected' => '删除 (:count)',
        'batch_delete_view_heading' => '确定要删除选中的 :count 条浏览记录吗？',
        'batch_delete_view_success' => '已删除选中的 :count 条浏览记录。',
        'unfollow' => '取消关注',
        'unfollow_heading' => '确定要取消关注吗？',
        'unfollow_success' => '已取消关注。',
        'unfollow_selected' => '取消关注 (:count)',
        'batch_unfollow_heading' => '确定要取消对选中的 :count 个用户的关注吗？',
        'batch_unfollow_success' => '已取消关注 :count 个用户。',
        'unlike' => '取消喜欢',
        'unlike_heading' => '确定要取消喜欢吗？',
        'unlike_success' => '已取消喜欢。',
        'unlike_selected' => '取消喜欢 (:count)',
        'batch_unlike_heading' => '确定要取消选中的 :count 条喜欢吗？',
        'batch_unlike_success' => '已取消选中的 :count 条喜欢。',
    ],
];
