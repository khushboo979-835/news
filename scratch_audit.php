<?php
require_once __DIR__ . '/config/db.php';
header('Content-Type: application/json; charset=utf-8');

$res = [
    'db_connected' => ($pdo !== null),
    'repair_result' => [],
    'news' => []
];

if ($pdo) {
    // Force repair directly and report
    try {
        $videoNewsConfig = [
            [
                'id' => 29,
                'slug' => 'aalmgnj-golibari-men-police-ka-khulasa',
                'type' => 'video_embed',
                'vid' => 'https://www.youtube.com/watch?v=9de_xASAKUE',
                'thumb' => 'https://img.youtube.com/vi/9de_xASAKUE/hqdefault.jpg',
                'duration' => '1:17'
            ],
            [
                'id' => 14,
                'slug' => 'patna-ke-sguna-mod-aur-gola-rod-ke-hotelon-men-chhapemari',
                'type' => 'video_embed',
                'vid' => 'https://www.youtube.com/watch?v=Nz-kHNLXhVY',
                'thumb' => 'https://img.youtube.com/vi/Nz-kHNLXhVY/hqdefault.jpg',
                'duration' => '2:45'
            ],
            [
                'id' => 19,
                'slug' => 'mlc-namankn-men-umda-smrthkon-ka-hujum',
                'type' => 'video_embed',
                'vid' => 'https://www.youtube.com/watch?v=nuC47zQshs4',
                'thumb' => 'https://img.youtube.com/vi/nuC47zQshs4/hqdefault.jpg',
                'duration' => '3:12'
            ],
            [
                'id' => 7,
                'slug' => 'patna-sivil-court-ke-mel-pr-dhamki',
                'type' => 'video_embed',
                'vid' => 'https://www.youtube.com/watch?v=k1tSrkVhUHo',
                'thumb' => 'https://img.youtube.com/vi/k1tSrkVhUHo/hqdefault.jpg',
                'duration' => '1:30'
            ]
        ];
        $updVidStmt = $pdo->prepare("UPDATE `news` SET `media_type` = :type, `media_url` = :vid, `video_url` = :vid, `thumbnail_url` = :thumb, `featured_image` = :thumb, `video_duration` = :dur WHERE `id` = :id OR `slug` = :slug");
        foreach ($videoNewsConfig as $vc) {
            $updVidStmt->execute([
                ':type' => $vc['type'],
                ':vid' => $vc['vid'],
                ':thumb' => $vc['thumb'],
                ':dur' => $vc['duration'],
                ':id' => $vc['id'],
                ':slug' => $vc['slug']
            ]);
            $res['repair_result']['video_' . $vc['id']] = 'updated to ' . $vc['vid'];
        }

        $imageMappings = [
            32 => 'news_1793024895_6738.jpg',
            31 => 'content_1793024818_1749.jpg',
            30 => 'news_1792890544_6556.jpg',
            26 => 'news_1792039400_1536.jpg',
            25 => 'content_1792044617_6331.png',
            24 => 'news_patna_police.jpg',
            23 => 'content_1792890571_1730.jpg',
            22 => 'news_durga_puja_pandal.jpg',
            20 => 'content_1792043165_2166.jpg',
            17 => 'news_1792742617_6047.jpg',
            16 => 'news_hospital_emergency.jpg',
            15 => 'news_1792742617_6047.jpg',
            13 => 'content_1792890585_7182.jpg',
            12 => 'content_1792890599_3707.jpg',
            11 => 'thumb_news_1792757175_6994.jpg',
            10 => 'news_cover_default.jpg',
            9  => 'news_patna_airport.jpg',
            8  => 'news_regent_cinema.jpg',
            6  => 'news_marine_drive_stunt.jpg',
            5  => 'news_kotwali_guessing_raid.jpg',
            4  => 'news_bike_theft_police.jpg',
            3  => 'news_patna_flood.jpg',
            2  => 'news_regent_cinema.jpg',
            1  => 'news_bihar_daroga_protest.jpg'
        ];
        $updImgStmt = $pdo->prepare("UPDATE `news` SET `media_type` = 'image', `media_url` = :img, `thumbnail_url` = :img, `featured_image` = :img, `video_url` = NULL WHERE `id` = :id");
        foreach ($imageMappings as $aid => $aimg) {
            $updImgStmt->execute([':img' => $aimg, ':id' => $aid]);
            $res['repair_result']['img_' . $aid] = 'updated to ' . $aimg;
        }

    } catch (Exception $e) {
        $res['repair_error'] = $e->getMessage();
    }

    $stmt = $pdo->query("SELECT id, headline, slug, media_type, media_url, thumbnail_url, featured_image, video_url, video_duration FROM news ORDER BY id DESC");
    $res['news'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

echo json_encode($res, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
