<?php
/*
Plugin Name: ApexChart Manager
Description: 管理画面でJSONデータを編集し、動的グラフ検出・リアルタイムプレビュー・ショートコードでApexChartsグラフを表示
Version: 1.1
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
                <div style="margin-bottom: 10px;">
                    <label for="graph-selector">グラフを選択:</label>
                    <select id="graph-selector" style="margin-left: 10px; padding: 5px;">
                        <option value="">-- グラフを選択してください --</option>
                    </select>
                </div>
                <div id="apexchart-preview" style="border: 1px solid #ddd; padding: 15px; background: #f9f9f9; min-height: 100px; border-radius: 4px;"></div>
            </div>
            
            <div style="flex: 1;">
                <h2>使用方法とデータ形式</h2>
                
                <h3>📊 基本的な使い方</h3>
                <ol>
                    <li><strong>JSONデータ編集:</strong> 左側のテキストエリアでJSONデータを編集</li>
                    <li><strong>リアルタイムプレビュー:</strong> グラフ選択ドロップダウンから表示したいグラフを選択</li>
                    <li><strong>保存:</strong> 「保存」ボタンでデータを保存</li>
                    <li><strong>表示:</strong> ショートコードで任意のページにグラフを表示</li>
                </ol>
        
                <h3>📝 JSON データ形式</h3>
                <p>JSONのトップレベルに任意のグラフ名でデータを配置できます:</p>
                <pre style="background: #f1f1f1; padding: 15px; border-radius: 5px; overflow-x: auto; font-size: 12px;">
{
    "任意のグラフ名1": {
        "labels": {
            "title": "グラフタイトル",
            "x_title": "X軸ラベル",
            "y_title": "Y軸ラベル", 
            "x_shaft": ["項目1", "項目2", "項目3"],
            "chart_type": "line",
            "chart_height": 350,
            "colors": ["#FF6B6B", "#4ECDC4"],
            "show_data_labels": false,
            "legend_position": "top"
        },
        "系列名1": [10, 20, 30],
        "系列名2": [15, 25, 35]
    },
    "任意のグラフ名2": { ... }
}</pre>

                <h3>🎨 ApexCharts主要オプション</h3>
                <p>labelsオブジェクト内で以下のオプションを設定できます:</p>
                <ul style="font-size: 13px;">
                    <li><code>chart_type</code>: line, area, bar, column, pie, donut など</li>
                    <li><code>chart_height</code>: グラフの高さ（数値）</li>
                    <li><code>chart_width</code>: グラフの幅（"100%"や数値）</li>
                    <li><code>colors</code>: カラー配列 ["#FF6B6B", "#4ECDC4"]</li>
                    <li><code>show_data_labels</code>: データラベル表示（true/false）</li>
                    <li><code>stroke_curve</code>: smooth, straight, stepline</li>
                    <li><code>legend_position</code>: top, bottom, left, right</li>
                    <li><code>title_align</code>: left, center, right</li>
                    <li><code>show_toolbar</code>: ツールバー表示（true/false）</li>
                    <li><code>show_grid</code>: グリッド表示（true/false）</li>
                    <li><code>markers</code>: マーカー設定 {"size": 7, "shape": "circle", "strokeColors": "#fff"}</li>
                </ul>

                <h3>📝 ショートコード</h3>
                <p><strong>新形式（推奨）:</strong></p>
                <code>[apexchart_manager id="グラフ名"]</code>
                
                <p><strong>パラメータ例:</strong></p>
                <code>[apexchart_manager id="sales2025" type="bar" height="400" colors="#FF6B6B,#4ECDC4"]</code>
                
                <p><strong>旧形式（互換性維持）:</strong></p>
                <code>[apexchart graph="グラフ名" type="line" height="350"]</code>
                
                <h3>📊 サンプルJSONデータ</h3>
                <p>以下のサンプルをコピーして試してください（6種類のグラフ例）:</p>
                <details style="margin: 10px 0;">
                    <summary style="cursor: pointer; padding: 5px; background: #e3f2fd; border-radius: 3px;">📋 サンプルJSONを表示</summary>
                    <pre style="background: #f8f9fa; padding: 15px; border-radius: 5px; overflow-x: auto; font-size: 11px; max-height: 400px; overflow-y: auto; margin-top: 10px;">
{
    "sales_monthly": {
        "labels": {
            "title": "2024年月別売上実績",
            "x_title": "月",
            "y_title": "売上金額（万円）",
            "x_shaft": ["1月", "2月", "3月", "4月", "5月", "6月", "7月", "8月", "9月", "10月", "11月", "12月"],
            "chart_type": "line",
            "chart_height": 350,
            "colors": ["#FF6B6B", "#4ECDC4", "#45B7D1"],
            "show_data_labels": true,
            "stroke_curve": "smooth"
        },
        "売上": [120, 150, 180, 200, 170, 220, 250, 280, 260, 300, 320, 350],
        "利益": [50, 60, 80, 90, 70, 100, 120, 140, 130, 160, 180, 200],
        "目標": [130, 140, 160, 180, 160, 200, 230, 260, 240, 280, 300, 330]
    },
    "user_analytics": {
        "labels": {
            "title": "ユーザー分析データ",
            "x_title": "週",
            "y_title": "ユーザー数",
            "x_shaft": ["第1週", "第2週", "第3週", "第4週", "第5週", "第6週"],
            "chart_type": "area",
            "chart_height": 400,
            "colors": ["#FF9F43", "#26de81", "#fc5c65"],
            "show_data_labels": false,
            "legend_position": "bottom"
        },
        "新規ユーザー": [100, 120, 110, 140, 160, 180],
        "リピートユーザー": [800, 850, 900, 920, 950, 980],
        "プレミアムユーザー": [50, 65, 80, 95, 110, 125]
    },
    "product_comparison": {
        "labels": {
            "title": "製品別売上比較",
            "x_title": "製品カテゴリ",
            "y_title": "売上数量",
            "x_shaft": ["スマートフォン", "タブレット", "ノートPC", "デスクトップ", "アクセサリ"],
            "chart_type": "bar",
            "chart_height": 350,
            "colors": ["#6c5ce7", "#fd79a8", "#fdcb6e"],
            "show_data_labels": true,
            "title_align": "left"
        },
        "Q1": [150, 80, 120, 60, 200],
        "Q2": [180, 95, 140, 70, 250],
        "Q3": [200, 110, 160, 85, 280]
    },
    "market_share": {
        "labels": {
            "title": "市場シェア分析",
            "chart_type": "pie",
            "chart_height": 400,
            "colors": ["#FF6B6B", "#4ECDC4", "#45B7D1", "#96CEB4", "#FECA57"],
            "legend_position": "right",
            "show_data_labels": true
        },
        "自社": [35],
        "競合A": [25],
        "競合B": [20],
        "競合C": [15],
        "その他": [5]
    },
    "performance_metrics": {
        "labels": {
            "title": "パフォーマンス指標推移",
            "x_title": "日付",
            "y_title": "指標値",
            "x_shaft": ["2024-01", "2024-02", "2024-03", "2024-04", "2024-05", "2024-06"],
            "chart_type": "column",
            "chart_height": 350,
            "colors": ["#e74c3c", "#f39c12", "#27ae60"],
            "show_data_labels": false,
            "show_grid": true
        },
        "コンバージョン率": [2.5, 3.1, 2.8, 3.5, 4.2, 3.9],
        "離脱率": [45, 42, 48, 40, 38, 35],
        "満足度": [7.2, 7.5, 7.1, 7.8, 8.1, 8.3]
    },
    "traffic_sources": {
        "labels": {
            "title": "トラフィックソース分析",
            "chart_type": "donut",
            "chart_height": 350,
            "colors": ["#3498db", "#e67e22", "#2ecc71", "#9b59b6", "#f1c40f"],
            "legend_position": "bottom",
            "show_data_labels": true
        },
        "オーガニック検索": [40],
        "ダイレクト": [25],
        "ソーシャルメディア": [15],
        "リファラル": [12],
        "広告": [8]
    },
    "regional_sales": {
        "labels": {
            "title": "地域別売上データ",
            "x_title": "地域",
            "y_title": "売上（百万円）",
            "x_shaft": ["北海道", "東北", "関東", "中部", "関西", "中国", "四国", "九州"],
            "chart_type": "line",
            "chart_height": 400,
            "colors": ["#FF6B6B", "#4ECDC4"],
            "stroke_curve": "straight",
            "show_data_labels": true,
            "show_toolbar": true
        },
        "今年": [25, 30, 120, 80, 100, 45, 35, 55],
        "昨年": [20, 25, 100, 70, 85, 40, 30, 50]
    }
}</pre>
                </details>
            </div>
        </div>
        
        <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
        <script>
        let currentChart = null;
        
        function updateGraphSelector() {
            let jsonStr = document.getElementById('apexchart_json').value;
            let selector = document.getElementById('graph-selector');
            
            // 現在の選択を保存
            let currentSelection = selector.value;
            
            // セレクターをクリア
            selector.innerHTML = '<option value="">-- グラフを選択してください --</option>';
            
            if (!jsonStr.trim()) {
                return;
            }
            
            try {
                let data = JSON.parse(jsonStr);
                let hasGraphs = false;
                
                // JSONのトップレベルキーをすべて検索
                for (let key in data) {
                    if (data.hasOwnProperty(key) && typeof data[key] === 'object' && data[key] !== null) {
                        // グラフデータらしいものを検出（labelsを含むか、配列データを含む）
                        let hasLabels = data[key].hasOwnProperty('labels');
                        let hasArrayData = false;
                        for (let subKey in data[key]) {
                            if (Array.isArray(data[key][subKey])) {
                                hasArrayData = true;
                                break;
                            }
                        }
                        
                        if (hasLabels || hasArrayData) {
                            let option = document.createElement('option');
                            option.value = key;
                            option.textContent = key;
                            selector.appendChild(option);
                            hasGraphs = true;
                        }
                    }
                }
                
                // 前の選択を復元（可能な場合）
                if (currentSelection && hasGraphs) {
                    for (let i = 0; i < selector.options.length; i++) {
                        if (selector.options[i].value === currentSelection) {
                            selector.value = currentSelection;
                            break;
                        }
                    }
                }
                
                // グラフが見つからない場合は最初のものを自動選択
                if (hasGraphs && !selector.value) {
                    selector.selectedIndex = 1; // 最初のグラフを選択
                }
                
            } catch (e) {
                // JSON解析エラー時は何もしない
            }
        }
        
        function renderSelectedChart() {
            let jsonStr = document.getElementById('apexchart_json').value;
            let selectedGraph = document.getElementById('graph-selector').value;
            let preview = document.getElementById('apexchart-preview');
            
            // 既存のチャートを破棄
            if (currentChart) {
                currentChart.destroy();
                currentChart = null;
            }
            
            preview.innerHTML = '';
            
            if (!jsonStr.trim()) {
                preview.innerHTML = '<span style="color: #666; font-style: italic;">JSONデータを入力するとプレビューが表示されます</span>';
                return;
            }
            
            if (!selectedGraph) {
                preview.innerHTML = '<span style="color: #666; font-style: italic;">グラフを選択してください</span>';
                return;
            }
            
            try {
                let data = JSON.parse(jsonStr);
                
                if (!data[selectedGraph]) {
                    preview.innerHTML = '<span style="color: red;">選択されたグラフ "' + selectedGraph + '" が見つかりません</span>';
                    return;
                }
                
                let g = data[selectedGraph];
                let series = [];
                
                // データ系列を構築
                for (let key in g) {
                    if (key === 'labels') continue;
                    if (Array.isArray(g[key])) {
                        series.push({ name: key, data: g[key] });
                    }
                }
                
                if (series.length === 0) {
                    preview.innerHTML = '<span style="color: #666; font-style: italic;">グラフ "' + selectedGraph + '" にデータ系列がありません</span>';
                    return;
                }
                
                let labels = g.labels || {};
                let options = {
                    chart: { 
                        type: labels.chart_type || 'line', 
                        height: labels.chart_height || 350,
                        toolbar: { show: true }
                    },
                    series: series,
                    xaxis: { 
                        categories: labels.x_shaft || [], 
                        title: { text: labels.x_title || '' } 
                    },
                    yaxis: { 
                        title: { text: labels.y_title || '' } 
                    },
                    title: { 
                        text: labels.title || selectedGraph + ' プレビュー',
                        align: 'center'
                    },
                    colors: labels.colors || undefined,
                    dataLabels: {
                        enabled: labels.show_data_labels !== false
                    },
                    stroke: {
                        curve: labels.stroke_curve || 'smooth'
                    },
                    legend: {
                        position: labels.legend_position || 'top'
                    }
                };
                
                // Add markers support
                if (labels.markers) {
                    options.markers = labels.markers;
                }
                
                let chartDiv = document.createElement('div');
                chartDiv.id = 'apexchart-preview-inner';
                preview.appendChild(chartDiv);
                
                currentChart = new ApexCharts(chartDiv, options);
                currentChart.render();
                
            } catch (e) {
                preview.innerHTML = '<span style="color: red;"><strong>JSONエラー:</strong> ' + e.message + '</span>';
            }
        }
        
        function renderChartFromJson() {
            updateGraphSelector();
            renderSelectedChart();
        }
        
        document.addEventListener('DOMContentLoaded', function() {
            let textarea = document.getElementById('apexchart_json');
            let selector = document.getElementById('graph-selector');
            
            if (textarea) {
                textarea.addEventListener('input', renderChartFromJson);
                renderChartFromJson(); // 初回読み込み時にも実行
            }
            
            if (selector) {
                selector.addEventListener('change', renderSelectedChart);
            }
        });
        </script>
    </div>
    <?php
}

// ショートコード [apexchart_manager id="graph1"]
add_shortcode('apexchart_manager', function($atts) {
    $atts = shortcode_atts([
        'id' => '',
        'type' => '',
        'height' => '',
        'width' => '',
        'colors' => '',
    ], $atts, 'apexchart_manager');

    if (empty($atts['id'])) {
        return '<div class="apexchart-error" style="color: red; border: 1px solid red; padding: 10px; background: #ffe6e6;">エラー: id パラメータが必須です。使用例: [apexchart_manager id="graph1"]</div>';
    }

    $json = get_option('apexchart_json', '');
    if (empty($json)) {
        return '<div class="apexchart-error" style="color: red; border: 1px solid red; padding: 10px; background: #ffe6e6;">エラー: JSONデータが設定されていません。管理画面で設定してください。</div>';
    }
    
    $data = json_decode($json, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        return '<div class="apexchart-error" style="color: red; border: 1px solid red; padding: 10px; background: #ffe6e6;">エラー: JSONデータが無効です。管理画面で確認してください。</div>';
    }
    
    if (!isset($data[$atts['id']])) {
        return '<div class="apexchart-error" style="color: red; border: 1px solid red; padding: 10px; background: #ffe6e6;">エラー: 指定されたグラフ "' . esc_html($atts['id']) . '" が見つかりません。</div>';
    }

    $g = $data[$atts['id']];
    
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
    
    $labels = isset($g['labels']) ? $g['labels'] : [];
    
    // ショートコードパラメータで上書き可能なオプション
    $chart_type = !empty($atts['type']) ? $atts['type'] : (isset($labels['chart_type']) ? $labels['chart_type'] : 'line');
    $chart_height = !empty($atts['height']) ? intval($atts['height']) : (isset($labels['chart_height']) ? intval($labels['chart_height']) : 350);
    $chart_width = !empty($atts['width']) ? $atts['width'] : (isset($labels['chart_width']) ? $labels['chart_width'] : '100%');
    
    // カラー設定
    $colors = [];
    if (!empty($atts['colors'])) {
        $colors = array_map('trim', explode(',', $atts['colors']));
    } elseif (isset($labels['colors']) && is_array($labels['colors'])) {
        $colors = $labels['colors'];
    }
    
    $options = [
        'chart' => [
            'type' => $chart_type,
            'height' => $chart_height,
            'width' => $chart_width,
            'toolbar' => ['show' => isset($labels['show_toolbar']) ? $labels['show_toolbar'] : true]
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
            'text' => isset($labels['title']) ? $labels['title'] : '',
            'align' => isset($labels['title_align']) ? $labels['title_align'] : 'center'
        ],
        'dataLabels' => [
            'enabled' => isset($labels['show_data_labels']) ? $labels['show_data_labels'] : false
        ],
        'stroke' => [
            'curve' => isset($labels['stroke_curve']) ? $labels['stroke_curve'] : 'smooth'
        ],
        'legend' => [
            'position' => isset($labels['legend_position']) ? $labels['legend_position'] : 'top'
        ]
    ];
    
    // カラー設定を追加
    if (!empty($colors)) {
        $options['colors'] = $colors;
    }
    
    // グリッド設定
    if (isset($labels['show_grid'])) {
        $options['grid'] = ['show' => $labels['show_grid']];
    }
    
    // マーカー設定
    if (isset($labels['markers'])) {
        $options['markers'] = $labels['markers'];
    }

    $chart_id = 'apexchart-' . uniqid();

    // ApexChartsライブラリを一度だけ読み込む
    static $apexcharts_loaded = false;
    $script_tag = '';
    if (!$apexcharts_loaded) {
        $apexcharts_js = 'https://cdn.jsdelivr.net/npm/apexcharts';
        $script_tag = '<script src="' . $apexcharts_js . '"></script>';
        $apexcharts_loaded = true;
    }

    $output = '
    <div id="' . esc_attr($chart_id) . '"></div>
    ' . $script_tag . '
    <script>
    document.addEventListener("DOMContentLoaded", function() {
        var options = ' . json_encode($options, JSON_UNESCAPED_UNICODE) . ';
        var chart = new ApexCharts(document.querySelector("#' . esc_js($chart_id) . '"), options);
        chart.render();
    });
    </script>
    ';
    return $output;
});

// 後方互換性のために古いショートコードも保持
add_shortcode('apexchart', function($atts) {
    $atts = shortcode_atts([
        'graph' => 'graph1',
        'type' => 'line',
        'height' => 350,
        'id' => 'apexchart-' . uniqid()
    ], $atts, 'apexchart');

    // 新しいショートコードにリダイレクト
    $new_atts = [
        'id' => $atts['graph'],
        'type' => $atts['type'],
        'height' => $atts['height']
    ];
    
    return do_shortcode('[apexchart_manager ' . http_build_query($new_atts, '', ' ') . ']');
});
?>