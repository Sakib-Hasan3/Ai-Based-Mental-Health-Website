<?php
// includes/mentor_helper.php

function getMentorTierBadge($tier) {
    $badges = [
        'silver' => ['label' => 'Silver Mentor', 'class' => 'silver', 'icon' => 'fa-star'],
        'gold' => ['label' => 'Gold Mentor', 'class' => 'gold', 'icon' => 'fa-star gold'],
        'platinum' => ['label' => 'Platinum Mentor', 'class' => 'platinum', 'icon' => 'fa-crown']
    ];
    return $badges[$tier] ?? $badges['silver'];
}

function getStatusBadge($status) {
    $badges = [
        'pending' => ['label' => 'অপেক্ষমান', 'class' => 'pending'],
        'confirmed' => ['label' => 'নিশ্চিত', 'class' => 'confirmed'],
        'completed' => ['label' => 'সম্পন্ন', 'class' => 'completed'],
        'cancelled' => ['label' => 'বাতিল', 'class' => 'cancelled'],
        'rejected' => ['label' => 'প্রত্যাখ্যাত', 'class' => 'rejected']
    ];
    return $badges[$status] ?? $badges['pending'];
}

function getSpecialties() {
    return [
        'Clinical Psychology' => 'ক্লিনিক্যাল সাইকোলজি',
        'Career Counseling' => 'ক্যারিয়ার কাউন্সেলিং',
        'Life Coaching' => 'লাইফ কোচিং',
        'Relationship Counseling' => 'সম্পর্ক কাউন্সেলিং',
        'Stress Management' => 'স্ট্রেস ম্যানেজমেন্ট',
        'Mindfulness' => 'মাইন্ডফুলনেস',
        'Parenting' => 'প্যারেন্টিং',
        'Academic Counseling' => 'একাডেমিক কাউন্সেলিং'
    ];
}
?>