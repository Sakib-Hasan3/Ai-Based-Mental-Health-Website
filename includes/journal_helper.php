<?php
// includes/journal_helper.php

function getMoodBadge($score) {
    $badges = [
        1 => ['label' => 'খুব খারাপ', 'class' => 'low', 'emoji' => '😢'],
        2 => ['label' => 'খারাপ', 'class' => 'low', 'emoji' => '😞'],
        3 => ['label' => 'অসন্তোষজনক', 'class' => 'low', 'emoji' => '😕'],
        4 => ['label' => 'মোটামুটি', 'class' => 'medium', 'emoji' => '😐'],
        5 => ['label' => 'সাধারণ', 'class' => 'medium', 'emoji' => '😌'],
        6 => ['label' => 'ভালো', 'class' => 'medium', 'emoji' => '🙂'],
        7 => ['label' => 'খুব ভালো', 'class' => 'high', 'emoji' => '😊'],
        8 => ['label' => 'দারুণ', 'class' => 'high', 'emoji' => '😄'],
        9 => ['label' => 'চমৎকার', 'class' => 'high', 'emoji' => '😍'],
        10 => ['label' => 'অসাধারণ', 'class' => 'high', 'emoji' => '🤩']
    ];
    return $badges[$score] ?? ['label' => 'সাধারণ', 'class' => 'medium', 'emoji' => '😐'];
}

function getCategoryInfo($category) {
    $categories = [
        'general' => ['name' => 'সাধারণ অনুভূতি', 'icon' => 'fa-feather-alt', 'color' => '#6366f1'],
        'work' => ['name' => 'কাজ/পড়াশোনা', 'icon' => 'fa-briefcase', 'color' => '#10b981'],
        'family' => ['name' => 'পরিবার', 'icon' => 'fa-home', 'color' => '#f59e0b'],
        'relationship' => ['name' => 'সম্পর্ক', 'icon' => 'fa-heart', 'color' => '#ef4444'],
        'goals' => ['name' => 'লক্ষ্য/প্রেরণা', 'icon' => 'fa-bullseye', 'color' => '#8b5cf6'],
        'stress' => ['name' => 'স্ট্রেস/উদ্বেগ', 'icon' => 'fa-brain', 'color' => '#ec489a'],
        'gratitude' => ['name' => 'কৃতজ্ঞতা', 'icon' => 'fa-hands-helping', 'color' => '#14b8a6'],
        'reflection' => ['name' => 'ব্যক্তিগত প্রতিফলন', 'icon' => 'fa-moon', 'color' => '#06b6d4'],
        'other' => ['name' => 'অন্যান্য', 'icon' => 'fa-ellipsis-h', 'color' => '#6b7280']
    ];
    return $categories[$category] ?? $categories['general'];
}

function getPreview($content, $length = 100) {
    $preview = strip_tags($content);
    if (strlen($preview) > $length) {
        $preview = substr($preview, 0, $length) . '...';
    }
    return $preview;
}

function validateJournalEntry($title, $content, $mood_score, $category) {
    $errors = [];
    
    if (empty($content)) {
        $errors[] = 'জার্নাল কন্টেন্ট খালি রাখা যাবে না';
    } elseif (strlen($content) < 5) {
        $errors[] = 'কমপক্ষে ৫ অক্ষর লিখুন';
    } elseif (strlen($content) > 10000) {
        $errors[] = 'কন্টেন্ট খুব বড় (সর্বোচ্চ ১০,০০০ অক্ষর)';
    }
    
    if ($title && strlen($title) > 200) {
        $errors[] = 'শিরোনাম খুব বড় (সর্বোচ্চ ২০০ অক্ষর)';
    }
    
    if ($mood_score && ($mood_score < 1 || $mood_score > 10)) {
        $errors[] = 'মুড স্কোর ১-১০ এর মধ্যে হতে হবে';
    }
    
    $allowed_categories = ['general', 'work', 'family', 'relationship', 'goals', 'stress', 'gratitude', 'reflection', 'other'];
    if ($category && !in_array($category, $allowed_categories)) {
        $errors[] = 'ক্যাটাগরি সঠিক নয়';
    }
    
    return $errors;
}
?>