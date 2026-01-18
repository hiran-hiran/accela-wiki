---
title: ヘルパーとその他
description: ヘルパー、アセット、初期化ファイルなどの配置規則
---

# ヘルパーとその他

MVC以外にも、Railsには様々なファイルを配置するためのディレクトリが用意されています。

## ヘルパー（Helpers）

### 配置場所

```
app/helpers/
```

### 命名規則

| コントローラ | ヘルパーファイル | モジュール名 |
|------------|----------------|------------|
| `UsersController` | `app/helpers/users_helper.rb` | `UsersHelper` |
| `PostsController` | `app/helpers/posts_helper.rb` | `PostsHelper` |

### 自動読み込み

コントローラに対応するヘルパーが自動的にビューで利用可能になります。

```ruby
# app/helpers/users_helper.rb
module UsersHelper
  def format_user_name(user)
    "#{user.first_name} #{user.last_name}"
  end
end
```

```erb
<!-- app/views/users/show.html.erb -->
<h1><%= format_user_name(@user) %></h1>
```

### ApplicationHelper

すべてのビューで利用可能な共通ヘルパーは `application_helper.rb` に定義します。

```ruby
# app/helpers/application_helper.rb
module ApplicationHelper
  def page_title(title)
    content_for(:title, title)
  end

  def full_title(page_title)
    base_title = "My App"
    page_title.blank? ? base_title : "#{page_title} | #{base_title}"
  end
end
```

## アセット（Assets）

### ディレクトリ構造

```
app/assets/
├── stylesheets/
│   ├── application.css
│   └── users.scss
├── javascripts/
│   ├── application.js
│   └── users.js
└── images/
    └── logo.png
```

### スタイルシート

```
app/assets/stylesheets/
├── application.css      # マニフェストファイル
├── users.scss           # ユーザー用スタイル
└── posts.scss           # 投稿用スタイル
```

コントローラに対応するスタイルシートが自動的に読み込まれます（Sprocketsの場合）。

### JavaScript

```
app/assets/javascripts/
├── application.js       # マニフェストファイル
├── users.js             # ユーザー用JS
└── posts.js             # 投稿用JS
```

### 画像

```
app/assets/images/
├── logo.png
├── icons/
│   ├── user.svg
│   └── post.svg
```

ビューでの使用：

```erb
<%= image_tag 'logo.png' %>
<%= image_tag 'icons/user.svg' %>
```

## ライブラリ（Lib）

### 配置場所

```
lib/
```

独自のライブラリやユーティリティクラスを配置します。

```ruby
# lib/text_formatter.rb
class TextFormatter
  def self.markdown_to_html(text)
    # 変換処理
  end
end
```

使用時は明示的に読み込みが必要（Rails 6以降）：

```ruby
# config/application.rb
config.autoload_paths += %W(#{config.root}/lib)
```

## 初期化ファイル（Initializers）

### 配置場所

```
config/initializers/
```

アプリケーション起動時に実行されるファイルを配置します。

```
config/initializers/
├── inflections.rb       # 単数形・複数形の変換ルール
├── session_store.rb     # セッション設定
├── filter_parameter_logging.rb  # ログのフィルタリング
└── custom_config.rb     # カスタム設定
```

### 例: カスタム設定

```ruby
# config/initializers/custom_config.rb
Rails.application.config.custom_setting = {
  api_key: ENV['API_KEY'],
  timeout: 30
}
```

使用：

```ruby
Rails.application.config.custom_setting[:api_key]
```

## ロケールファイル（Locales）

### 配置場所

```
config/locales/
```

多言語対応のための翻訳ファイルを配置します。

```
config/locales/
├── en.yml               # 英語
├── ja.yml               # 日本語
└── models/
    └── user.ja.yml      # ユーザーモデル用（日本語）
```

### 例: 日本語ロケール

```yaml
# config/locales/ja.yml
ja:
  activerecord:
    models:
      user: ユーザー
      post: 投稿
    attributes:
      user:
        name: 名前
        email: メールアドレス
      post:
        title: タイトル
        content: 本文
  helpers:
    submit:
      create: 登録する
      update: 更新する
```

使用：

```ruby
I18n.t('activerecord.models.user')  # => "ユーザー"
```

## ジョブ（Jobs）

### 配置場所

```
app/jobs/
```

バックグラウンドジョブを配置します。

```ruby
# app/jobs/user_notification_job.rb
class UserNotificationJob < ApplicationJob
  queue_as :default

  def perform(user)
    # 通知処理
  end
end
```

使用：

```ruby
UserNotificationJob.perform_later(@user)
```

## メイラー（Mailers）

### 配置場所

```
app/mailers/
app/views/[メイラー名]/
```

```
app/
├── mailers/
│   └── user_mailer.rb
└── views/
    └── user_mailer/
        ├── welcome_email.html.erb
        └── welcome_email.text.erb
```

### 例

```ruby
# app/mailers/user_mailer.rb
class UserMailer < ApplicationMailer
  def welcome_email(user)
    @user = user
    mail(to: @user.email, subject: 'Welcome!')
  end
end
```

ビューが自動的に使用されます：
- `app/views/user_mailer/welcome_email.html.erb`（HTML版）
- `app/views/user_mailer/welcome_email.text.erb`（テキスト版）

## テスト（Tests/Specs）

### Minitestの場合

```
test/
├── models/
│   └── user_test.rb
├── controllers/
│   └── users_controller_test.rb
└── fixtures/
    └── users.yml
```

### RSpecの場合

```
spec/
├── models/
│   └── user_spec.rb
├── controllers/
│   └── users_controller_spec.rb
├── requests/
│   └── users_spec.rb
└── factories/
    └── users.rb
```

## データベース（Database）

### マイグレーション

```
db/migrate/
└── 20240118000000_create_users.rb
```

ファイル名：タイムスタンプ + アンダースコア + 説明

```ruby
# db/migrate/20240118000000_create_users.rb
class CreateUsers < ActiveRecord::Migration[7.0]
  def change
    create_table :users do |t|
      t.string :name
      t.timestamps
    end
  end
end
```

### シードデータ

```ruby
# db/seeds.rb
User.create(name: "Admin", email: "admin@example.com")
Post.create(title: "Hello", content: "World")
```

実行：

```bash
$ rails db:seed
```

## 設定ファイル（Config）

### 環境ごとの設定

```
config/environments/
├── development.rb       # 開発環境
├── test.rb              # テスト環境
└── production.rb        # 本番環境
```

### データベース設定

```yaml
# config/database.yml
development:
  adapter: postgresql
  database: myapp_development

test:
  adapter: postgresql
  database: myapp_test

production:
  adapter: postgresql
  database: myapp_production
```

### ルーティング

```ruby
# config/routes.rb
Rails.application.routes.draw do
  resources :users
  resources :posts
end
```

## まとめ

| 用途 | ディレクトリ | 説明 |
|------|------------|------|
| ヘルパー | `app/helpers/` | ビューで使用するヘルパーメソッド |
| アセット | `app/assets/` | CSS、JavaScript、画像 |
| ライブラリ | `lib/` | 独自ライブラリ |
| 初期化 | `config/initializers/` | 起動時設定 |
| ロケール | `config/locales/` | 多言語対応 |
| ジョブ | `app/jobs/` | バックグラウンドジョブ |
| メイラー | `app/mailers/` | メール送信 |
| テスト | `test/` or `spec/` | テストコード |
| マイグレーション | `db/migrate/` | データベース変更履歴 |

規約に従うことで：
- ファイルの配置場所が明確
- 自動読み込みが機能
- チーム開発がスムーズ
- Railsの機能を最大限活用
