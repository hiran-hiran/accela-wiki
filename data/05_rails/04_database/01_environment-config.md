---
title: 環境と接続設定
description: 3つの標準環境とdatabase.ymlの規約
---

# 環境と接続設定

Railsでは、開発・テスト・本番の3つの標準環境が用意されており、環境ごとに異なる設定を持つことができます。

## 3つの標準環境

### 環境一覧

| 環境 | 用途 | データベース名例 |
|------|------|----------------|
| `development` | 開発環境 | `myapp_development` |
| `test` | テスト実行環境 | `myapp_test` |
| `production` | 本番環境 | `myapp_production` |

### 環境の切り替え

```bash
# 開発環境（デフォルト）
$ rails server
$ rails console

# テスト環境
$ RAILS_ENV=test rails console
$ RAILS_ENV=test rails db:migrate

# 本番環境
$ RAILS_ENV=production rails server
$ RAILS_ENV=production rails console
```

Rakeタスクやコマンドは、環境変数 `RAILS_ENV` で環境を指定します。

## database.yml

### 配置場所

```
config/database.yml
```

### 基本構造

```yaml
default: &default
  adapter: postgresql
  encoding: unicode
  pool: <%= ENV.fetch("RAILS_MAX_THREADS") { 5 } %>

development:
  <<: *default
  database: myapp_development

test:
  <<: *default
  database: myapp_test

production:
  <<: *default
  database: myapp_production
  username: myapp
  password: <%= ENV['MYAPP_DATABASE_PASSWORD'] %>
```

### 環境ごとの設定

#### development（開発環境）

```yaml
development:
  adapter: postgresql
  database: myapp_development
  host: localhost
  username: postgres
  password: password
  pool: 5
  timeout: 5000
```

特徴：
- ローカルのデータベースを使用
- パスワードは平文でも可（開発用）
- ログが詳細に出力される

#### test（テスト環境）

```yaml
test:
  adapter: postgresql
  database: myapp_test
  host: localhost
  username: postgres
  password: password
```

特徴：
- テスト専用のデータベース
- テスト実行のたびにデータがクリアされる
- トランザクションでロールバックされる

#### production（本番環境）

```yaml
production:
  adapter: postgresql
  database: myapp_production
  username: <%= ENV['DATABASE_USERNAME'] %>
  password: <%= ENV['DATABASE_PASSWORD'] %>
  host: <%= ENV['DATABASE_HOST'] %>
```

特徴：
- 環境変数から認証情報を取得（セキュリティ）
- 本番用のデータベースサーバーに接続
- ログは最小限

## データベース命名規約

### 標準パターン

```
[アプリ名]_[環境名]
```

例：
- `myapp_development`
- `myapp_test`
- `myapp_production`

### ジェネレータで自動生成

```bash
$ rails new myapp --database=postgresql
```

`config/database.yml` が自動生成され、適切なデータベース名が設定されます。

## アダプター（Adapter）

### 主要なアダプター

| アダプター | データベース | Gem |
|----------|------------|-----|
| `postgresql` | PostgreSQL | `pg` |
| `mysql2` | MySQL | `mysql2` |
| `sqlite3` | SQLite | `sqlite3` |

### PostgreSQLの例

```yaml
development:
  adapter: postgresql
  encoding: unicode
  database: myapp_development
  pool: 5
  username: postgres
  password: password
  host: localhost
```

### MySQLの例

```yaml
development:
  adapter: mysql2
  encoding: utf8mb4
  database: myapp_development
  pool: 5
  username: root
  password: password
  host: localhost
```

### SQLiteの例

```yaml
development:
  adapter: sqlite3
  database: db/development.sqlite3
  pool: 5
  timeout: 5000
```

## 接続プール

### pool オプション

同時接続数の上限を設定します。

```yaml
development:
  adapter: postgresql
  database: myapp_development
  pool: <%= ENV.fetch("RAILS_MAX_THREADS") { 5 } %>
```

デフォルトは5で、環境変数 `RAILS_MAX_THREADS` で上書き可能です。

### 推奨値

- 開発環境: 5
- テスト環境: 5
- 本番環境: スレッド数 + α（例: 10-20）

## 環境変数の使用

### セキュリティのベストプラクティス

本番環境では、認証情報を環境変数から取得します。

```yaml
production:
  adapter: postgresql
  database: <%= ENV['DATABASE_NAME'] %>
  username: <%= ENV['DATABASE_USERNAME'] %>
  password: <%= ENV['DATABASE_PASSWORD'] %>
  host: <%= ENV['DATABASE_HOST'] %>
  port: <%= ENV.fetch('DATABASE_PORT') { 5432 } %>
```

### .envファイル（dotenv）

開発環境で環境変数を管理するには、`dotenv-rails` gemを使用します。

```ruby
# Gemfile
gem 'dotenv-rails', groups: [:development, :test]
```

```
# .env
DATABASE_USERNAME=postgres
DATABASE_PASSWORD=password
DATABASE_HOST=localhost
```

注意: `.env` は `.gitignore` に追加してコミットしない

## 複数データベース

### Rails 6以降

複数のデータベースに接続できます。

```yaml
production:
  primary:
    adapter: postgresql
    database: myapp_primary
    username: <%= ENV['PRIMARY_DB_USERNAME'] %>
    password: <%= ENV['PRIMARY_DB_PASSWORD'] %>

  analytics:
    adapter: postgresql
    database: myapp_analytics
    username: <%= ENV['ANALYTICS_DB_USERNAME'] %>
    password: <%= ENV['ANALYTICS_DB_PASSWORD'] %>
    replica: true
```

モデルで接続先を指定：

```ruby
class AnalyticsRecord < ApplicationRecord
  connects_to database: { writing: :analytics, reading: :analytics }
end

class Report < AnalyticsRecord
end
```

## データベース操作コマンド

### 作成

```bash
$ rails db:create
```

`database.yml` に基づいてデータベースを作成します。

### 削除

```bash
$ rails db:drop
```

### リセット

```bash
$ rails db:reset
# = db:drop + db:create + db:migrate + db:seed
```

### 環境指定

```bash
$ RAILS_ENV=test rails db:create
$ RAILS_ENV=production rails db:migrate
```

## まとめ

- Railsは `development`, `test`, `production` の3つの標準環境を持つ
- `config/database.yml` で環境ごとの接続設定を管理
- データベース名は `[アプリ名]_[環境名]` が規約
- 本番環境では環境変数を使用してセキュリティを確保
- `adapter` でデータベースの種類を指定
- `pool` で接続プール数を管理
- 環境は `RAILS_ENV` で切り替える
