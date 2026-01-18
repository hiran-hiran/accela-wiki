---
title: パスヘルパー
description: URL生成ヘルパーの命名規則と使い方
---

# パスヘルパー

Railsでは、ルートを定義すると自動的にパスヘルパーとURLヘルパーが生成されます。これにより、URLを直接記述せずにリンクを作成できます。

## パスヘルパーとURLヘルパー

### 違い

| ヘルパー | 出力 | 用途 |
|---------|------|------|
| パスヘルパー（`_path`） | 相対パス | 内部リンク |
| URLヘルパー（`_url`） | 絶対URL | メール、リダイレクト、API |

### 例

```ruby
users_path      # => "/users"
users_url       # => "http://example.com/users"

user_path(@user)  # => "/users/1"
user_url(@user)   # => "http://example.com/users/1"
```

## 命名規則

### resources で生成されるヘルパー

```ruby
resources :users
```

| アクション | パスヘルパー | URL例 |
|----------|------------|-------|
| index | `users_path` | `/users` |
| show | `user_path(@user)` | `/users/1` |
| new | `new_user_path` | `/users/new` |
| edit | `edit_user_path(@user)` | `/users/1/edit` |
| create | `users_path`（POST） | `/users` |
| update | `user_path(@user)`（PATCH） | `/users/1` |
| destroy | `user_path(@user)`（DELETE） | `/users/1` |

注目点：
- 一覧と作成は同じ `users_path`（HTTPメソッドで区別）
- 詳細・更新・削除は同じ `user_path(@user)`（HTTPメソッドで区別）

## ビューでの使用

### link_to でリンクを作成

```erb
<!-- 一覧ページへのリンク -->
<%= link_to "ユーザー一覧", users_path %>
<!-- <a href="/users">ユーザー一覧</a> -->

<!-- 詳細ページへのリンク -->
<%= link_to "詳細", user_path(@user) %>
<!-- <a href="/users/1">詳細</a> -->

<!-- 編集ページへのリンク -->
<%= link_to "編集", edit_user_path(@user) %>
<!-- <a href="/users/1/edit">編集</a> -->

<!-- 新規作成ページへのリンク -->
<%= link_to "新規作成", new_user_path %>
<!-- <a href="/users/new">新規作成</a> -->
```

### button_to で削除ボタン

```erb
<%= button_to "削除", user_path(@user), method: :delete, data: { confirm: "本当に削除しますか?" } %>
```

### form_with でフォーム

```erb
<%= form_with model: @user do |f| %>
  <%= f.text_field :name %>
  <%= f.submit %>
<% end %>
```

自動的に適切なパスとHTTPメソッドが設定されます：
- 新規作成: `POST /users`
- 更新: `PATCH /users/1`

## コントローラでの使用

### redirect_to

```ruby
def create
  @user = User.new(user_params)
  if @user.save
    redirect_to user_path(@user)  # または @user
    # => /users/1 へリダイレクト
  else
    render :new
  end
end

def destroy
  @user.destroy
  redirect_to users_path
  # => /users へリダイレクト
end
```

### ショートカット記法

モデルインスタンスを直接渡すこともできます：

```ruby
redirect_to @user       # user_path(@user) と同じ
redirect_to [@post]     # post_path(@post) と同じ
redirect_to users_url   # 絶対URLでリダイレクト
```

## パラメータの追加

### クエリパラメータ

```ruby
users_path(page: 2, per: 10)
# => "/users?page=2&per=10"

user_path(@user, format: :json)
# => "/users/1.json"
```

### ハッシュオプション

```ruby
user_path(@user, anchor: 'profile')
# => "/users/1#profile"

users_path(sort: 'name', order: 'asc')
# => "/users?sort=name&order=asc"
```

## カスタムルートのヘルパー

### 名前付きルート

```ruby
get '/about', to: 'pages#about', as: 'about'
# => about_path, about_url が生成される
```

```erb
<%= link_to "About", about_path %>
<!-- <a href="/about">About</a> -->
```

### コレクション/メンバールート

```ruby
resources :posts do
  member do
    post :publish
  end

  collection do
    get :archived
  end
end
```

生成されるヘルパー：

```ruby
publish_post_path(@post)   # => "/posts/1/publish"
archived_posts_path        # => "/posts/archived"
```

## ネストしたリソース

### ネストの例

```ruby
resources :users do
  resources :posts
end
```

生成されるヘルパー：

```ruby
user_posts_path(@user)           # => "/users/1/posts"
user_post_path(@user, @post)     # => "/users/1/posts/1"
new_user_post_path(@user)        # => "/users/1/posts/new"
edit_user_post_path(@user, @post) # => "/users/1/posts/1/edit"
```

### 配列記法

```ruby
# これらは同じ意味
user_post_path(@user, @post)
[@user, @post]

# ビューで使用
<%= link_to "詳細", [@user, @post] %>
<%= form_with model: [@user, @post] do |f| %>
```

## ルートの確認

### rails routes コマンド

```bash
# すべてのルートを表示
$ rails routes

# 特定のコントローラのルートのみ
$ rails routes -c users

# 特定のヘルパー名で検索
$ rails routes -g user_path
```

### コンソールで確認

```ruby
# Railsコンソールでヘルパーを試す
$ rails console

# app オブジェクトを使う
app.users_path
# => "/users"

app.user_path(User.first)
# => "/users/1"
```

## まとめ

- `_path` は相対パス、`_url` は絶対URL
- `resources :users` で `users_path`, `user_path` などが自動生成
- `link_to`, `redirect_to`, `form_with` で使用
- モデルインスタンスを直接渡せる（`@user` → `user_path(@user)`）
- クエリパラメータやアンカーも追加可能
- `rails routes` でルート一覧を確認
- 規約に従うことで、URLの変更が容易になる
