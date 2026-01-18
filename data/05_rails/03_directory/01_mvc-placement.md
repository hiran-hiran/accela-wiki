---
title: MVCの配置
description: モデル、ビュー、コントローラの配置ルールとファイル命名
---

# MVCの配置

Railsでは、MVCアーキテクチャの各要素を決められたディレクトリに配置します。

## ディレクトリ構造

### 基本構成

```
app/
├── models/           # モデル
├── controllers/      # コントローラ
├── views/            # ビュー
├── helpers/          # ヘルパー
├── mailers/          # メイラー
├── jobs/             # ジョブ
└── channels/         # Action Cable
```

## モデル（Models）

### 配置場所

```
app/models/
```

### 命名規則

| モデルクラス名 | ファイル名 | テーブル名 |
|--------------|-----------|-----------|
| `User` | `app/models/user.rb` | `users` |
| `Post` | `app/models/post.rb` | `posts` |
| `TodoItem` | `app/models/todo_item.rb` | `todo_items` |

ポイント：
- クラス名: 単数形のキャメルケース（`TodoItem`）
- ファイル名: 単数形のスネークケース（`todo_item.rb`）
- ディレクトリ直下に配置

### ファイルの内容

```ruby
# app/models/user.rb
class User < ApplicationRecord
  has_many :posts
  validates :email, presence: true
end
```

### ネームスペース付きモデル

サブディレクトリを使ってモデルをグループ化できます。

```
app/models/
└── admin/
    └── user.rb
```

```ruby
# app/models/admin/user.rb
class Admin::User < ApplicationRecord
end
```

## コントローラ（Controllers）

### 配置場所

```
app/controllers/
```

### 命名規則

| コントローラクラス名 | ファイル名 | URLパス |
|------------------|-----------|---------|
| `UsersController` | `app/controllers/users_controller.rb` | `/users` |
| `PostsController` | `app/controllers/posts_controller.rb` | `/posts` |
| `TodoItemsController` | `app/controllers/todo_items_controller.rb` | `/todo_items` |

ポイント：
- クラス名: 複数形のキャメルケース + `Controller`（`UsersController`）
- ファイル名: 複数形のスネークケース + `_controller.rb`（`users_controller.rb`）

### ファイルの内容

```ruby
# app/controllers/users_controller.rb
class UsersController < ApplicationController
  def index
    @users = User.all
  end

  def show
    @user = User.find(params[:id])
  end
end
```

### ネームスペース付きコントローラ

```
app/controllers/
└── admin/
    └── users_controller.rb
```

```ruby
# app/controllers/admin/users_controller.rb
class Admin::UsersController < ApplicationController
  def index
    @users = User.all
  end
end
```

ルーティング：

```ruby
namespace :admin do
  resources :users  # /admin/users
end
```

## ビュー（Views）

### 配置場所

```
app/views/[コントローラ名]/
```

### 命名規則

コントローラのアクション名に対応するビューファイルを配置します。

```
app/views/
└── users/
    ├── index.html.erb      # UsersController#index
    ├── show.html.erb       # UsersController#show
    ├── new.html.erb        # UsersController#new
    ├── edit.html.erb       # UsersController#edit
    └── _form.html.erb      # パーシャル（先頭に_）
```

| アクション | ビューファイル |
|----------|--------------|
| `UsersController#index` | `app/views/users/index.html.erb` |
| `UsersController#show` | `app/views/users/show.html.erb` |
| `UsersController#new` | `app/views/users/new.html.erb` |
| `UsersController#edit` | `app/views/users/edit.html.erb` |

### 自動レンダリング

コントローラで明示的に `render` を呼ばなくても、アクション名に対応するビューが自動的にレンダリングされます。

```ruby
# app/controllers/users_controller.rb
class UsersController < ApplicationController
  def index
    @users = User.all
    # 自動的に app/views/users/index.html.erb がレンダリングされる
  end
end
```

### パーシャル

部分テンプレートは先頭に `_` を付けます。

```
app/views/users/
└── _form.html.erb
```

```erb
<!-- app/views/users/new.html.erb -->
<h1>新規ユーザー</h1>
<%= render 'form', user: @user %>
```

```erb
<!-- app/views/users/_form.html.erb -->
<%= form_with model: user do |f| %>
  <%= f.text_field :name %>
  <%= f.submit %>
<% end %>
```

### レイアウト

```
app/views/layouts/
└── application.html.erb
```

デフォルトで `application.html.erb` がレイアウトとして使用されます。

```erb
<!-- app/views/layouts/application.html.erb -->
<!DOCTYPE html>
<html>
<head>
  <title>My App</title>
  <%= csrf_meta_tags %>
  <%= stylesheet_link_tag 'application' %>
</head>
<body>
  <%= yield %>
</body>
</html>
```

### ネームスペース付きビュー

```
app/views/
└── admin/
    └── users/
        ├── index.html.erb
        └── show.html.erb
```

対応するコントローラ：`Admin::UsersController`

## コントローラとビューの対応

### 例: UsersController

```ruby
# app/controllers/users_controller.rb
class UsersController < ApplicationController
  def index
    @users = User.all
    # => app/views/users/index.html.erb
  end

  def show
    @user = User.find(params[:id])
    # => app/views/users/show.html.erb
  end

  def new
    @user = User.new
    # => app/views/users/new.html.erb
  end

  def create
    @user = User.new(user_params)
    if @user.save
      redirect_to @user
    else
      render :new  # => app/views/users/new.html.erb
    end
  end
end
```

### ビューの手動指定

```ruby
def index
  @users = User.all
  render :custom_index  # app/views/users/custom_index.html.erb
end

def show
  @user = User.find(params[:id])
  render 'shared/profile'  # app/views/shared/profile.html.erb
end
```

## 複雑な構造の例

### ネストしたリソース

```
app/
├── controllers/
│   ├── users_controller.rb
│   └── posts_controller.rb
├── models/
│   ├── user.rb
│   └── post.rb
└── views/
    ├── users/
    │   ├── index.html.erb
    │   └── show.html.erb
    └── posts/
        ├── index.html.erb
        └── show.html.erb
```

ルーティング：

```ruby
resources :users do
  resources :posts
end
```

コントローラ：

```ruby
# app/controllers/posts_controller.rb
class PostsController < ApplicationController
  def index
    @user = User.find(params[:user_id])
    @posts = @user.posts
    # => app/views/posts/index.html.erb
  end
end
```

## まとめ

| 要素 | ディレクトリ | ファイル名規則 | クラス名規則 |
|------|------------|--------------|-------------|
| モデル | `app/models/` | 単数形スネークケース | 単数形キャメルケース |
| コントローラ | `app/controllers/` | 複数形スネークケース + `_controller.rb` | 複数形キャメルケース + `Controller` |
| ビュー | `app/views/[コントローラ名]/` | アクション名 + `.html.erb` | - |
| パーシャル | `app/views/[コントローラ名]/` | `_` + 名前 + `.html.erb` | - |

規約に従うことで：
- 自動読み込みが機能
- ビューが自動レンダリング
- ファイルの配置場所が明確
- チーム開発がスムーズ
