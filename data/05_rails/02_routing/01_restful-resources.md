---
title: RESTfulリソース
description: 7つの標準アクションとルート自動生成
---

# RESTfulリソース

Railsの `resources` を使うと、RESTfulな7つの標準アクションへのルートが自動生成されます。

## 基本形

### resources の宣言

```ruby
# config/routes.rb
Rails.application.routes.draw do
  resources :users
end
```

この1行で、7つのルートが自動生成されます。

## 7つの標準アクション

### 生成されるルート一覧

| HTTPメソッド | パス | コントローラ#アクション | パスヘルパー | 用途 |
|------------|------|---------------------|------------|------|
| GET | `/users` | `users#index` | `users_path` | 一覧表示 |
| GET | `/users/new` | `users#new` | `new_user_path` | 新規作成フォーム |
| POST | `/users` | `users#create` | `users_path` | 作成処理 |
| GET | `/users/:id` | `users#show` | `user_path(@user)` | 詳細表示 |
| GET | `/users/:id/edit` | `users#edit` | `edit_user_path(@user)` | 編集フォーム |
| PATCH/PUT | `/users/:id` | `users#update` | `user_path(@user)` | 更新処理 |
| DELETE | `/users/:id` | `users#destroy` | `user_path(@user)` | 削除処理 |

### ルートの確認

```bash
$ rails routes | grep users
    users GET    /users(.:format)          users#index
          POST   /users(.:format)          users#create
 new_user GET    /users/new(.:format)      users#new
edit_user GET    /users/:id/edit(.:format) users#edit
     user GET    /users/:id(.:format)      users#show
          PATCH  /users/:id(.:format)      users#update
          PUT    /users/:id(.:format)      users#update
          DELETE /users/:id(.:format)      users#destroy
```

## コントローラの実装

### 7つのアクション

```ruby
# app/controllers/users_controller.rb
class UsersController < ApplicationController
  # 一覧表示
  def index
    @users = User.all
  end

  # 詳細表示
  def show
    @user = User.find(params[:id])
  end

  # 新規作成フォーム
  def new
    @user = User.new
  end

  # 新規作成処理
  def create
    @user = User.new(user_params)
    if @user.save
      redirect_to @user
    else
      render :new
    end
  end

  # 編集フォーム
  def edit
    @user = User.find(params[:id])
  end

  # 更新処理
  def update
    @user = User.find(params[:id])
    if @user.update(user_params)
      redirect_to @user
    else
      render :edit
    end
  end

  # 削除処理
  def destroy
    @user = User.find(params[:id])
    @user.destroy
    redirect_to users_path
  end

  private

  def user_params
    params.require(:user).permit(:name, :email)
  end
end
```

## アクションの制限

### 特定のアクションのみ生成

```ruby
# index と show のみ
resources :users, only: [:index, :show]
```

生成されるルート：

```
GET /users       users#index
GET /users/:id   users#show
```

### 特定のアクションを除外

```ruby
# destroy を除外
resources :users, except: [:destroy]
```

## 単数リソース

### resource（複数形ではない）

ユーザーのプロフィールなど、1つしか存在しないリソースの場合：

```ruby
resource :profile
```

生成されるルート：

| HTTPメソッド | パス | コントローラ#アクション | 用途 |
|------------|------|---------------------|------|
| GET | `/profile/new` | `profiles#new` | 新規作成フォーム |
| POST | `/profile` | `profiles#create` | 作成処理 |
| GET | `/profile` | `profiles#show` | 詳細表示 |
| GET | `/profile/edit` | `profiles#edit` | 編集フォーム |
| PATCH/PUT | `/profile` | `profiles#update` | 更新処理 |
| DELETE | `/profile` | `profiles#destroy` | 削除処理 |

注目点：
- `index` アクションがない
- URLに `:id` が含まれない
- パスヘルパーは単数形（`profile_path`）

## コレクションルートとメンバールート

### コレクションルート（全体に対するアクション）

```ruby
resources :users do
  collection do
    get :search  # GET /users/search
  end
end
```

または

```ruby
resources :users do
  get :search, on: :collection
end
```

### メンバールート（個別に対するアクション）

```ruby
resources :users do
  member do
    post :activate  # POST /users/:id/activate
  end
end
```

または

```ruby
resources :users do
  post :activate, on: :member
end
```

### 使用例

```ruby
resources :posts do
  member do
    post :publish    # POST /posts/:id/publish
    post :unpublish  # POST /posts/:id/unpublish
  end

  collection do
    get :archived    # GET /posts/archived
  end
end
```

## 名前空間

### namespace

```ruby
namespace :admin do
  resources :users
end
```

生成されるルート：

```
GET    /admin/users          admin/users#index
GET    /admin/users/:id      admin/users#show
...
```

コントローラの配置：

```
app/controllers/admin/users_controller.rb
```

```ruby
class Admin::UsersController < ApplicationController
  # ...
end
```

### scope

URLは変更せず、コントローラの配置だけ変える：

```ruby
scope module: 'admin' do
  resources :users  # /users → admin/users#index
end
```

## まとめ

- `resources :users` で7つのRESTfulルートが自動生成
- `only` / `except` で必要なアクションのみ生成可能
- `resource`（単数形）でIDなしのリソースを定義
- `collection` / `member` でカスタムアクションを追加
- `namespace` / `scope` でルートをグループ化
- 規約に従うことで、最小限の記述で完全なCRUDが実現
