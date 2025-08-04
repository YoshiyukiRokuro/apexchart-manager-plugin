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
    $error_message = '';
    $success_message = '';
    
    if (isset($_POST['apexchart_json'])) {
        check_admin_referer('save_apexchart_json');
        $json_input = stripslashes($_POST['apexchart_json']);
        
        // JSON検証
        if (empty($json_input)) {
            $error_message = 'JSONデータが空です。';
        } else {
            $decoded = json_decode($json_input, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                $error_message = 'JSONフォーマットエラー: ' . json_last_error_msg();
            } elseif (!is_array($decoded)) {
                $error_message = 'JSONはオブジェクト形式である必要があります。';
            } else {
                // JSON形式は正しいので保存
                update_option('apexchart_json', $json_input);
                $success_message = 'JSONデータを保存しました！';
            }
        }
    }
    $json = get_option('apexchart_json', '');
    ?>
    <div class="wrap">
        <h1>ApexChart JSONデータ管理</h1>
        
        <?php if ($error_message): ?>
        <div class="notice notice-error">
            <p><strong>エラー:</strong> <?php echo esc_html($error_message); ?></p>
        </div>
        <?php endif; ?>
        
        <?php if ($success_message): ?>
        <div class="notice notice-success is-dismissible">
            <p><?php echo esc_html($success_message); ?></p>
        </div>
        <?php endif; ?>
        
        <div style="display: flex; gap: 20px;">
            <div style="flex: 1;">
                <h2>JSONデータ編集</h2>
                <form method="post">
                    <?php wp_nonce_field('save_apexchart_json'); ?>
                    <textarea id="apexchart_json" name="apexchart_json" style="width:100%;height:400px;font-family:monospace;font-size:13px;border:1px solid #ddd;padding:10px;" placeholder="JSONデータを入力してください..."><?php echo esc_textarea($json); ?></textarea>
                    <p>
                        <input type="submit" value="保存" class="button button-primary">
                        <span style="margin-left: 10px; color: #666;">
                            <strong>ヒント:</strong> JSON形式を確認してから保存してください
                        </span>
                    </p>
                </form>
                
                <h3>リアルタイムプレビュー</h3>
                <div id="apexchart-preview" style="border: 1px solid #ddd; padding: 15px; background: #f9f9f9; min-height: 100px; border-radius: 4px;"></div>
            </div>
            
            <div style="flex: 1;">
                <h2>使用方法とデータ形式</h2>
        <h3>JSON データ形式</h3>
        <p>以下の形式でJSONデータを入力してください：</p>
        <pre style="background: #f1f1f1; padding: 15px; border-radius: 5px; overflow-x: auto;">
{
    "graph1": {
        "labels": {
            "title": "売上グラフ",
            "x_title": "月",
            "y_title": "売上 (万円)",
            "x_shaft": ["1月", "2月", "3月", "4月", "5月", "6月"]
        },
        "売上": [120, 150, 180, 200, 170, 220],
        "利益": [50, 60, 80, 90, 70, 100]
    },
    "graph2": {
        "labels": {
            "title": "ユーザー数推移",
            "x_title": "週",
            "y_title": "ユーザー数",
            "x_shaft": ["第1週", "第2週", "第3週", "第4週"]
        },
        "新規": [100, 120, 110, 140],
        "既存": [800, 850, 900, 920]
    }
}
        </pre>
        <p><strong>使用方法:</strong> <code>[apexchart graph="graph1" type="line" height="350"]</code></p>
        <p><strong>パラメータ:</strong></p>
        <ul>
            <li><code>graph</code>: グラフ名 (必須)</li>
            <li><code>type</code>: グラフタイプ (line, area, bar, column など)</li>
            <li><code>height</code>: グラフの高さ (ピクセル)</li>
        </ul>
            </div>
        </div>
        
        <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
        <script>
        function renderChartFromJson() {
            let jsonStr = document.getElementById('apexchart_json').value;
            let preview = document.getElementById('apexchart-preview');
            preview.innerHTML = '';
            
            if (!jsonStr.trim()) {
                preview.innerHTML = '<span style="color: #666; font-style: italic;">JSONデータを入力するとプレビューが表示されます</span>';
                return;
            }
            
            try {
                let data = JSON.parse(jsonStr);
                // 例: graph1をプレビュー
                if (data.graph1) {
                    let g = data.graph1;
                    let series = [];
                    for (let key in g) {
                        if (key === 'labels') continue;
                        if (Array.isArray(g[key])) {
                            series.push({ name: key, data: g[key] });
                        }
                    }
                    let labels = g.labels || {};
                    let options = {
                        chart: { type: 'line', height: 350 },
                        series: series,
                        xaxis: { 
                            categories: labels.x_shaft || [], 
                            title: { text: labels.x_title || '' } 
                        },
                        yaxis: { title: { text: labels.y_title || '' } },
                        title: { text: labels.title || 'graph1プレビュー' }
                    };
                    let chartDiv = document.createElement('div');
                    chartDiv.id = 'apexchart-preview-inner';
                    preview.appendChild(chartDiv);
                    let chart = new ApexCharts(chartDiv, options);
                    chart.render();
                } else {
                    preview.innerHTML = '<span style="color: #666; font-style: italic;">graph1が見つかりません。プレビューにはgraph1のデータが必要です。</span>';
                }
            } catch (e) {
                preview.innerHTML = '<span style="color: red;"><strong>JSONが不正です:</strong> ' + e.message + '</span>';
            }
        }
        
        document.addEventListener('DOMContentLoaded', function() {
            let textarea = document.getElementById('apexchart_json');
            if (textarea) {
                textarea.addEventListener('input', renderChartFromJson);
                renderChartFromJson(); // 初回読み込み時にも実行
            }
        });
        </script>
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
    if (empty($json)) {
        return '<div class="apexchart-error" style="color: red; border: 1px solid red; padding: 10px; background: #ffe6e6;">エラー: JSONデータが設定されていません。管理画面で設定してください。</div>';
    }
    
    $data = json_decode($json, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        return '<div class="apexchart-error" style="color: red; border: 1px solid red; padding: 10px; background: #ffe6e6;">エラー: JSONデータが無効です。管理画面で確認してください。</div>';
    }
    
    if (!isset($data[$atts['graph']])) {
        return '<div class="apexchart-error" style="color: red; border: 1px solid red; padding: 10px; background: #ffe6e6;">エラー: 指定されたグラフ "' . esc_html($atts['graph']) . '" が見つかりません。</div>';
    }

    $g = $data[$atts['graph']];
    
    // ラベルの検証
    if (!isset($g['labels']) || !is_array($g['labels'])) {
        return '<div class="apexchart-error" style="color: red; border: 1px solid red; padding: 10px; background: #ffe6e6;">エラー: グラフデータにlabelsが設定されていません。</div>';
    }
    
    $series = [];
    foreach ($g as $key => $val) {
        if ($key === 'labels') continue;
        if (!is_array($val)) continue;
        $series[] = [
            'name' => $key,
            'data' => $val
        ];
    }
    
    if (empty($series)) {
        return '<div class="apexchart-error" style="color: red; border: 1px solid red; padding: 10px; background: #ffe6e6;">エラー: グラフデータが空です。</div>';
    }
    
    $labels = $g['labels'];
    $options = [
        'chart' => [
            'type' => $atts['type'],
            'height' => intval($atts['height'])
        ],
        'series' => $series,
        'xaxis' => [
            'categories' => isset($labels['x_shaft']) ? $labels['x_shaft'] : [],
            'title' => ['text' => isset($labels['x_title']) ? $labels['x_title'] : '']
        ],
        'yaxis' => [
            'title' => ['text' => isset($labels['y_title']) ? $labels['y_title'] : '']
        ],
        'title' => [
            'text' => isset($labels['title']) ? $labels['title'] : ''
        ]
    ];

    // ApexChartsライブラリを一度だけ読み込む
    static $apexcharts_loaded = false;
    $script_tag = '';
    if (!$apexcharts_loaded) {
        $apexcharts_js = 'https://cdn.jsdelivr.net/npm/apexcharts';
        $script_tag = '<script src="' . $apexcharts_js . '"></script>';
        $apexcharts_loaded = true;
    }

    $output = '
    <div id="' . esc_attr($atts['id']) . '"></div>
    ' . $script_tag . '
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