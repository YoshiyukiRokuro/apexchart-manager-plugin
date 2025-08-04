# ApexChart Manager Plugin

WordPressでApexChartsグラフを簡単に管理・表示するプラグインです。管理画面でJSONデータを編集し、リアルタイムプレビューを確認しながら、ショートコードで任意のページにグラフを表示できます。

## 🌟 主な機能

- **動的グラフ検出**: JSONデータから任意の数・名前のグラフを自動検出
- **リアルタイムプレビュー**: 編集中のJSONデータを即座にプレビュー表示
- **柔軟なグラフ選択**: ドロップダウンで表示するグラフを選択可能
- **豊富なApexChartsオプション**: chart.type, colors, title, legend など多数のオプションをサポート
- **ショートコード対応**: `[apexchart_manager id="グラフ名"]` で簡単表示
- **後方互換性**: 既存の `[apexchart]` ショートコードも継続サポート

## 📦 インストール

1. プラグインファイルをWordPressの `/wp-content/plugins/` ディレクトリにアップロード
2. WordPressの管理画面でプラグインを有効化
3. 管理画面メニューの「ApexChart JSON」から設定開始

## 🚀 使用方法

### 1. 基本的な使い方

1. **管理画面アクセス**: WordPress管理画面 > ApexChart JSON
2. **JSONデータ編集**: 左側のテキストエリアでデータを編集
3. **プレビュー確認**: グラフ選択ドロップダウンで表示を確認
4. **データ保存**: 「保存」ボタンでデータを保存
5. **ショートコード使用**: 投稿・ページでショートコードを使用

### 2. JSON データ形式

JSONのトップレベルに任意のグラフ名でデータを配置できます：

```json
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
}
```

### 3. ApexCharts主要オプション

`labels` オブジェクト内で以下のオプションを設定できます：

| オプション | 説明 | 例 |
|------------|------|-----|
| `chart_type` | グラフタイプ | line, area, bar, column, pie, donut |
| `chart_height` | グラフの高さ（数値） | 350 |
| `chart_width` | グラフの幅 | "100%", 500 |
| `colors` | カラー配列 | ["#FF6B6B", "#4ECDC4"] |
| `show_data_labels` | データラベル表示 | true, false |
| `stroke_curve` | 線の曲線タイプ | smooth, straight, stepline |
| `legend_position` | 凡例位置 | top, bottom, left, right |
| `title_align` | タイトル位置 | left, center, right |
| `show_toolbar` | ツールバー表示 | true, false |
| `show_grid` | グリッド表示 | true, false |

### 4. ショートコード

#### 新形式（推奨）
```
[apexchart_manager id="グラフ名"]
```

**パラメータ例:**
```
[apexchart_manager id="sales2025" type="bar" height="400" colors="#FF6B6B,#4ECDC4"]
```

#### 旧形式（互換性維持）
```
[apexchart graph="グラフ名" type="line" height="350"]
```

## 📊 サンプルデータ

### 基本例（2グラフ）
```json
{
    "sales_data": {
        "labels": {
            "title": "月別売上データ",
            "x_title": "月",
            "y_title": "売上（万円）",
            "x_shaft": ["1月", "2月", "3月", "4月", "5月", "6月"],
            "chart_type": "line",
            "colors": ["#FF6B6B", "#4ECDC4"]
        },
        "売上": [120, 150, 180, 200, 170, 220],
        "利益": [50, 60, 80, 90, 70, 100]
    },
    "user_stats": {
        "labels": {
            "title": "ユーザー統計",
            "x_title": "週",
            "y_title": "ユーザー数", 
            "x_shaft": ["第1週", "第2週", "第3週", "第4週"],
            "chart_type": "bar",
            "chart_height": 300
        },
        "新規": [100, 120, 110, 140],
        "既存": [800, 850, 900, 920]
    }
}
```

### 完全版サンプル（7種類のグラフ）
管理画面のサンプルJSONセクションから完全版をコピーできます。以下の7種類のグラフサンプルが含まれています：

1. **sales_monthly** - 線グラフ（月別売上実績）
2. **user_analytics** - エリアグラフ（ユーザー分析）
3. **product_comparison** - 棒グラフ（製品比較）
4. **market_share** - 円グラフ（市場シェア）
5. **performance_metrics** - 縦棒グラフ（パフォーマンス指標）
6. **traffic_sources** - ドーナツグラフ（トラフィックソース）
7. **regional_sales** - 線グラフ（地域別売上）

## 🎨 グラフタイプ一覧

- **line** - 線グラフ
- **area** - エリアグラフ  
- **bar** - 横棒グラフ
- **column** - 縦棒グラフ
- **pie** - 円グラフ
- **donut** - ドーナツグラフ
- **scatter** - 散布図
- **bubble** - バブルチャート
- **heatmap** - ヒートマップ
- **radar** - レーダーチャート

## 🔧 技術仕様

- **要件**: WordPress 4.0以上、PHP 5.6以上
- **外部ライブラリ**: ApexCharts.js (CDN経由)
- **データ形式**: JSON
- **対応ブラウザ**: モダンブラウザ全般

## 📝 更新履歴

### Version 1.1 (最新)
- 動的グラフ検出機能の追加
- リアルタイムプレビュー機能の強化
- 新ショートコード `[apexchart_manager]` の追加
- ApexChartsオプションの大幅拡張
- 7種類のサンプルグラフを追加
- ドキュメント大幅充実

### Version 1.0
- 基本機能の実装
- `[apexchart]` ショートコード対応

## 📄 ライセンス

このプラグインはMITライセンスの下で公開されています。

## 👤 作者

**Yoshiyuki Rokuro**

---

*ApexChart Manager Plugin で美しいグラフを簡単に作成・管理しましょう！*
