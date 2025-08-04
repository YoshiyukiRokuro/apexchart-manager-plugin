<?php
/*
Plugin Name: ApexChart Manager
Description: 管理画面でJSONデータを編集し、ショートコードでApexChartsグラフを表示
Version: 1.0
Author: Yoshiyuki Rokuro
*/

// 管理画面メニュー追加
add_action('admin_menu', function() {
    add_menu_page(
        'ApexChart JSON管理',
        'ApexChart JSON',
        'manage_options',
        'apexchart-json-manager',
        'apexchart_json_manager_page'
    );
});

// 設定ページの中身
function apexchart_json_manager_page() {
    if (isset($_POST['apexchart_json'])) {
        check_admin_referer('save_apexchart_json');
        update_option('apexchart_json', stripslashes($_POST['apexchart_json']));
        echo '<div class="updated"><p>保存しました！</p></div>';
    }
    $json = get_option('apexchart_json', '');
    ?>
    <div class="wrap">
        <h2>ApexChart JSONデータ管理</h2>
        <form method="post">
            <?php wp_nonce_field('save_apexchart_json'); ?>
            <textarea name="apexchart_json" style="width:100%;height:400px;"><?php echo esc_textarea($json); ?></textarea>
            <p>
                <input type="submit" value="保存" class="button button-primary">
            </p>
        </form>
        <p>JSON例:<br>
        <pre>
{
    "graph1": {...},
    "graph2": {...}
}
        </pre>
        </p>
    </div>
    <?php
}

// ショートコード [apexchart graph="graph1"]
add_shortcode('apexchart', function($atts) {
    $atts = shortcode_atts([
        'graph' => 'graph1',
        'type' => 'line',
        'height' => 350,
        'id' => 'apexchart-' . uniqid()
    ], $atts, 'apexchart');

    $json = get_option('apexchart_json', '');
    if (!$json) return 'データ未設定';
    $data = json_decode($json, true);
    if (!isset($data[$atts['graph']])) return '指定グラフなし';

    $g = $data[$atts['graph']];
    $series = [];
    foreach ($g as $key => $val) {
        if ($key === 'labels') continue;
        $series[] = [
            'name' => $key,
            'data' => $val
        ];
    }
    $labels = $g['labels'];
    $options = [
        'chart' => [
            'type' => $atts['type'],
            'height' => intval($atts['height'])
        ],
        'series' => $series,
        'xaxis' => [
            'categories' => $labels['x_shaft'],
            'title' => ['text' => $labels['x_title']]
        ],
        'yaxis' => [
            'title' => ['text' => $labels['y_title']]
        ],
        'title' => [
            'text' => $labels['title']
        ]
    ];

    $apexcharts_js = 'https://cdn.jsdelivr.net/npm/apexcharts';
    $output = '
    <div id="' . esc_attr($atts['id']) . '"></div>
    <script src="' . $apexcharts_js . '"></script>
    <script>
    document.addEventListener("DOMContentLoaded", function() {
        var options = ' . json_encode($options, JSON_UNESCAPED_UNICODE) . ';
        var chart = new ApexCharts(document.querySelector("#' . esc_js($atts['id']) . '"), options);
        chart.render();
    });
    </script>
    ';
    return $output;
});
?>