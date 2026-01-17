---
title: Accela Wiki
description:
---

# Accela Wiki

Accela Wikiは、PHP SPAフレームワークの**Accela**で構築されたマークダウンベースのWikiシステムです。<br>
マークダウンテキストを配置するだけで、自動的にHTMLとサイトマップが生成されます。<br>
基盤となるAccelaは、依存関係のないPHPで開発されているため、通常のレンタルサーバでも動作させることができます。

**※Accelaの動作確認を含むテストプロジェクトのため、今後は大幅な変更や削除が行われる可能性があります。**

## 3ステップで始める

1. `git clone https://github.com/accela-framework/wiki.git && composer install`
2. `data/`にマークダウンファイルを配置
3. ブラウザでアクセス

それだけでWikiが完成します。

## デモ

このサイト自体がAccela Wikiで構築されています。サイドバーのナビゲーションや階層構造は、`data/`ディレクトリの構成から自動生成されています。

```
data/
├── index.md           → /
├── 01_installation.md → /installation
├── 02_guide/
│   ├── index.md       → /guide/
│   ├── 02_basic-usage.md → /guide/basic-usage
│   └── ...
└── 03_accela/
    ├── index.md       → /accela/
    └── ...
```

数字プレフィックス（`01_`など）は並び順の制御に使われ、URLからは除外されます。

## 主な特徴

| 特徴 | Accela Wiki |
|------|-------------|
| データベース | 不要 |
| ビルド | 不要（SSGも可能） |
| 設定ファイル | 最小限 |
| デプロイ | FTPアップロードのみ |

### マークダウン
コンテンツは全てマークダウンで記述します。

### ファイルベースルーティング
ファイル名、ディレクトリ名がそのままURLに対応するため、システムを気にせずドキュメントの作成に専念できます。
データベースも必要ありません。

### 階層構造
ディレクトリを作れば、再帰的に階層構造が自動生成されます。

## 動作環境

- PHP 8.2以上
- [Accela](https://accela.in-green-spot.com)

## ライセンス

MIT
