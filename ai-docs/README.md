# Accela Wiki - AI向けドキュメント

このディレクトリには、AIがAccela Wikiのコンテンツを生成・編集する際に必要な情報がまとめられています。

## Accela Wikiとは

マークダウンファイルを`data/`ディレクトリに配置するだけで動作するWikiシステムです。

- **PHPのみで動作**（Node.js不要）
- **データベース不要**
- **ビルド不要**（SSGも可能）
- **ファイルベースルーティング**

## クイックスタート

1. `data/`ディレクトリにマークダウンファイルを作成
2. ブラウザでアクセス → 自動的にWikiとして表示

## ドキュメント一覧

| ファイル | 内容 |
|----------|------|
| [structure.md](structure.md) | ディレクトリ構成・ファイル配置ルール |
| [frontmatter.md](frontmatter.md) | Front Matter仕様 |
| [routing.md](routing.md) | URL生成ルール |
| [sidebar.md](sidebar.md) | サイドバー表示ロジック |
| [examples.md](examples.md) | よくあるパターン・サンプル |
| [guidelines.md](guidelines.md) | 禁止事項・推奨パターン |

## 最初に読むべきファイル

1. `structure.md` - ファイル配置の基本
2. `frontmatter.md` - 必須のメタデータ形式
3. `guidelines.md` - やってはいけないこと

## 対象ディレクトリ

```
accela-wiki/
├── data/          ← コンテンツを配置（編集対象）
├── app/           ← フレームワーク（編集禁止）
├── assets/        ← CSS/JS/画像
└── ai-docs/       ← このドキュメント
```
