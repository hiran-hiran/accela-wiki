# よくあるパターン・サンプル

## パターン1: シンプルなWiki

```
data/
├── index.md              ← トップページ
├── 01_about.md           ← 単独ページ
├── 02_features.md
└── 03_contact.md
```

## パターン2: セクション分け

```
data/
├── index.md
├── 01_getting-started/
│   ├── index.md          ← セクショントップ
│   ├── 01_installation.md
│   └── 02_configuration.md
├── 02_guide/
│   ├── index.md
│   ├── 01_basics.md
│   └── 02_advanced.md
└── 03_reference/
    ├── index.md
    └── 01_api.md
```

## パターン3: グループ名のみ（リンクなし）

```
data/
├── index.md
└── 01_api/
    ├── 01_users.md       ← サイドバー: api > users
    ├── 02_posts.md       ← サイドバー: api > posts
    └── 03_comments.md    ← サイドバー: api > comments
```

`index.md`がないので「api」はリンクなしのグループ名として表示されます。

## パターン4: タイトルのみのグループ

```
data/
└── 01_endpoints/
    ├── index.md          ← titleのみ、本文なし
    ├── 01_get.md
    └── 02_post.md
```

`01_endpoints/index.md`:
```markdown
---
title: APIエンドポイント
---
```

「APIエンドポイント」がグループ名として表示されますが、リンクにはなりません。

## サンプルファイル

### トップページ

`data/index.md`:
```markdown
---
title: プロジェクト名
description: プロジェクトの概要説明
---

# プロジェクト名

プロジェクトの紹介文...

## 特徴

- 特徴1
- 特徴2
- 特徴3

## クイックスタート

1. ステップ1
2. ステップ2
3. ステップ3
```

### セクションページ

`data/02_guide/index.md`:
```markdown
---
title: 使い方ガイド
description: 基本的な使い方を説明します
---

# 使い方ガイド

このセクションでは基本的な使い方を説明します。

## 目次

- [基本操作](basic-usage) - 基本的な操作方法
- [応用](advanced) - 応用的な使い方
```

### 子ページ

`data/02_guide/01_basic-usage.md`:
```markdown
---
title: 基本操作
description: 基本的な操作方法を説明します
---

# 基本操作

## はじめに

基本的な操作方法について説明します。

## 操作手順

### 手順1

説明...

### 手順2

説明...
```

## 内部リンク

同じWiki内のページへのリンク：

```markdown
[インストール](/installation)
[基本操作](/guide/basic-usage)
[トップページ](/)
```

相対パスも使用可能：

```markdown
[次のページ](./02_next-page)
[親ディレクトリ](../)
```
