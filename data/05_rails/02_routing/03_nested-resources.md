---
title: ネストしたリソース
description: リソースの階層構造とルーティング
---

# ネストしたリソース

関連するリソースを階層的に表現する場合、ネストしたルーティングを使用します。

## 基本形

### ネストの宣言

```ruby
# config/routes.rb
resources :users do
  resources :posts
end
```

これにより、「ユーザーに属する投稿」という関係性がURLに表現されます。

## 生成されるルート

### ネストされたリソースのルート一覧

| HTTPメソッド | パス | コントローラ#アクション | パスヘルパー |
|------------|------|---------------------|------------|
| GET | `/users/:user_id/posts` | `posts#index` | `user_posts_path(@user)` |
| GET | `/users/:user_id/posts/new` | `posts#new` | `new_user_post_path(@user)` |
| POST | `/users/:user_id/posts` | `posts#create` | `user_posts_path(@user)` |
| GET | `/users/:user_id/posts/:id` | `posts#show` | `user_post_path(@user, @post)` |
| GET | `/users/:user_id/posts/:id/edit` | `posts#edit` | `edit_user_post_path(@user, @post)` |
| PATCH/PUT | `/users/:user_id/posts/:id` | `posts#update` | `user_post_path(@user, @post)` |
| DELETE | `/users/:user_id/posts/:id` | `posts#destroy` | `user_post_path(@user, @post)` |

### 確認

```bash
$ rails routes | grep posts
     user_posts GET    /users/:user_id/posts(.:format)          posts#index
                POST   /users/:user_id/posts(.:format)          posts#create
  new_user_post GET    /users/:user_id/posts/new(.:format)      posts#new
 edit_user_post GET    /users/:user_id/posts/:id/edit(.:format) posts#edit
      user_post GET    /users/:user_id/posts/:id(.:format)      posts#show
                PATCH  /users/:user_id/posts/:id(.:format)      posts#update
                PUT    /users/:user_id/posts/:id(.:format)      posts#update
                DELETE /users/:user_id/posts/:id(.:format)      posts#destroy
```

## コントローラの実装

### 親リソースの取得

```ruby
# app/controllers/posts_controller.rb
class PostsController < ApplicationController
  before_action :set_user

  def index
    @posts = @user.posts
  end

  def show
    @post = @user.posts.find(params[:id])
  end

  def new
    @post = @user.posts.build
  end

  def create
    @post = @user.posts.build(post_params)
    if @post.save
      redirect_to [@user, @post]
    else
      render :new
    end
  end

  def edit
    @post = @user.posts.find(params[:id])
  end

  def update
    @post = @user.posts.find(params[:id])
    if @post.update(post_params)
      redirect_to [@user, @post]
    else
      render :edit
    end
  end

  def destroy
    @post = @user.posts.find(params[:id])
    @post.destroy
    redirect_to user_posts_path(@user)
  end

  private

  def set_user
    @user = User.find(params[:user_id])
  end

  def post_params
    params.require(:post).permit(:title, :content)
  end
end
```

ポイント：
- `params[:user_id]` で親のIDを取得
- `@user.posts` を経由してアクセス（関連付けを活用）

## ビューでの使用

### リンクの作成

```erb
<!-- 一覧ページへのリンク -->
<%= link_to "投稿一覧", user_posts_path(@user) %>
<!-- /users/1/posts -->

<!-- 詳細ページへのリンク -->
<%= link_to "詳細", user_post_path(@user, @post) %>
<!-- /users/1/posts/1 -->

<!-- 配列記法（推奨） -->
<%= link_to "詳細", [@user, @post] %>
<!-- /users/1/posts/1 -->

<!-- 新規作成ページ -->
<%= link_to "新規投稿", new_user_post_path(@user) %>
<!-- /users/1/posts/new -->

<!-- 編集ページ -->
<%= link_to "編集", edit_user_post_path(@user, @post) %>
<!-- /users/1/posts/1/edit -->
```

### フォーム

```erb
<%= form_with model: [@user, @post] do |f| %>
  <%= f.text_field :title %>
  <%= f.text_area :content %>
  <%= f.submit %>
<% end %>
```

自動的に適切なパスが設定されます：
- 新規作成: `POST /users/1/posts`
- 更新: `PATCH /users/1/posts/1`

## シャローネスティング

### 問題点

深くネストすると、URLが冗長になります：

```ruby
resources :users do
  resources :posts do
    resources :comments  # /users/1/posts/2/comments/3
  end
end
```

### shallow オプション

`shallow: true` を使うと、コレクションアクション以外はネストを浅くできます。

```ruby
resources :users do
  resources :posts, shallow: true
end
```

生成されるルート：

| アクション | パス | 説明 |
|----------|------|------|
| index | `/users/:user_id/posts` | ネストあり |
| new | `/users/:user_id/posts/new` | ネストあり |
| create | `/users/:user_id/posts` | ネストあり |
| show | `/posts/:id` | ネストなし |
| edit | `/posts/:id/edit` | ネストなし |
| update | `/posts/:id` | ネストなし |
| destroy | `/posts/:id` | ネストなし |

理由：
- `index`, `new`, `create` は親リソースのコンテキストが必要
- `show`, `edit`, `update`, `destroy` は`:id`で一意に特定できる

### shallow ブロック

```ruby
shallow do
  resources :users do
    resources :posts
  end
end
```

または

```ruby
resources :users, shallow: true do
  resources :posts
end
```

## ネストの制限

### only / except との組み合わせ

```ruby
resources :users do
  resources :posts, only: [:index, :show, :new, :create]
end
```

### 1階層のみネスト（推奨）

深すぎるネストは避け、1階層にとどめることが推奨されます。

```ruby
# 非推奨
resources :users do
  resources :posts do
    resources :comments
  end
end

# 推奨
resources :users do
  resources :posts
end

resources :posts do
  resources :comments
end
```

## 複数のネスト

### 同じリソースを異なる親にネスト

```ruby
resources :users do
  resources :posts
end

resources :categories do
  resources :posts
end
```

この場合、コントローラで分岐が必要です：

```ruby
class PostsController < ApplicationController
  before_action :set_parent

  def index
    @posts = @parent.posts
  end

  private

  def set_parent
    if params[:user_id]
      @parent = User.find(params[:user_id])
    elsif params[:category_id]
      @parent = Category.find(params[:category_id])
    end
  end
end
```

## コレクション/メンバールートのネスト

### ネスト内でのカスタムアクション

```ruby
resources :users do
  resources :posts do
    member do
      post :publish
    end

    collection do
      get :drafts
    end
  end
end
```

生成されるルート：

```ruby
publish_user_post_path(@user, @post)
# => "/users/1/posts/2/publish"

drafts_user_posts_path(@user)
# => "/users/1/posts/drafts"
```

## concern を使った共通ルート

### 複数のリソースで共通のネストを定義

```ruby
concern :commentable do
  resources :comments
end

resources :posts, concerns: :commentable
resources :videos, concerns: :commentable
```

これは以下と同等です：

```ruby
resources :posts do
  resources :comments
end

resources :videos do
  resources :comments
end
```

## まとめ

- `resources` をネストして階層構造を表現
- パスヘルパーは `user_post_path(@user, @post)` のように親を含む
- 配列記法 `[@user, @post]` が推奨
- `shallow: true` で不要なネストを削減
- ネストは1階層まで推奨
- コントローラでは `params[:user_id]` で親を取得
- `form_with model: [@user, @post]` で自動的にネストしたパスを生成
