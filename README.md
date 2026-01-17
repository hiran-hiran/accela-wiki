# Accela Wiki

マークダウンファイルを置くだけで動作するWikiシステムです。

- **PHPのみで動作** - Node.js不要、レンタルサーバでもOK
- **データベース不要** - ファイルベースで完結
- **ビルド不要** - 置いて即公開（SSGも可能）
- **AIと相性抜群** - マークダウン生成 → 配置で完成

## クイックスタート

```bash
git clone https://github.com/accela-framework/wiki.git
cd wiki
composer install
```

`data/`にマークダウンファイルを配置してブラウザでアクセス。

## 動作環境

- PHP 8.2以上
- Webサーバー（Apache / Nginx）

## ディレクトリ構成

```
data/
├── index.md           → /
├── 01_page.md         → /page
└── 02_section/
    ├── index.md       → /section/
    └── 01_child.md    → /section/child
```

- 数字プレフィックス（`01_`等）で並び順を制御
- URLからはプレフィックスが除外される

## AIでドキュメントを生成

`ai-docs/`ディレクトリにAI向けの仕様書が含まれています。
AIツール（Claude Code、Cursor等）に読み込ませることで、Wikiコンテンツを自動生成できます。

詳細は [AIを使った開発](/ai/) を参照してください。

## ドキュメント

- [インストールガイド](/installation)
- [基本的な使い方](/guide/)
- [Accelaの機能](/accela/)

## ライセンス

MIT
