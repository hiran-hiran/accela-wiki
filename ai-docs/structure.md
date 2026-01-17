# ディレクトリ構成・ファイル配置ルール

## 基本構成

```
data/
├── index.md                    ← トップページ（必須）
├── 01_section-name.md          ← 単独ページ
├── 02_section-name/            ← セクション（ディレクトリ）
│   ├── index.md                ← セクショントップ
│   ├── 01_page-name.md         ← 子ページ
│   └── 02_subsection/          ← サブセクション
│       └── index.md
└── ...
```

## ファイル命名規則

### 数字プレフィックス

```
{数字}_{名前}.md
例: 01_getting-started.md, 02_installation.md
```

- **目的**: サイドバーの並び順を制御
- **形式**: 2桁推奨（01, 02, ... 99）
- **URLからは除外**: `01_guide.md` → `/guide`

### 名前部分

- **ケバブケース推奨**: `getting-started`, `basic-usage`
- **小文字のみ**
- **スペース禁止**

| OK | NG |
|----|-----|
| `getting-started.md` | `Getting Started.md` |
| `basic-usage.md` | `basicUsage.md` |
| `api-reference.md` | `api_reference.md` |

## index.mdの役割

### index.mdがある場合

```
02_guide/
├── index.md       ← /guide/ としてアクセス可能
└── 01_basics.md   ← /guide/basics
```

- ディレクトリに対応するページが存在
- サイドバーの親項目がリンクになる
- URLは末尾に`/`が付く: `/guide/`

### index.mdがない場合

```
02_guide/
├── 01_basics.md   ← /guide/basics
└── 02_advanced.md ← /guide/advanced
```

- ディレクトリ名がそのままグループ名として表示
- サイドバーの親項目はリンクなし（テキストのみ）

## 階層の深さ

- **推奨**: 3階層まで
- **対応**: 無制限（技術的には何階層でも可能）
- **注意**: 深すぎるとサイドバーの可読性が下がる

```
data/                    ← 1階層目
├── 01_section/          ← 2階層目
│   └── 01_subsection/   ← 3階層目（ここまで推奨）
│       └── 01_deep/     ← 4階層目以降（可能だが非推奨）
```
