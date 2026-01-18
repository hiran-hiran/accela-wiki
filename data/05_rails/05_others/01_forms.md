---
title: フォーム規約
description: form_withとモデルの自動推論
---

# フォーム規約

Railsのフォームは、モデルと連携して自動的に適切なHTTPメソッドとパスを生成します。

## form_with

### 基本形

```erb
<%= form_with model: @user do |f| %>
  <%= f.text_field :name %>
  <%= f.email_field :email %>
  <%= f.submit %>
<% end %>
```

### 自動推論される内容

#### 新規作成の場合

```ruby
@user = User.new
```

```html
<form action="/users" method="post">
  <input type="text" name="user[name]" />
  <input type="email" name="user[email]" />
  <input type="submit" value="Create User" />
</form>
```

- アクション: `POST /users`
- ボタンラベル: "Create User"

#### 更新の場合

```ruby
@user = User.find(1)
```

```html
<form action="/users/1" method="post">
  <input type="hidden" name="_method" value="patch" />
  <input type="text" name="user[name]" value="John" />
  <input type="email" name="user[email]" value="john@example.com" />
  <input type="submit" value="Update User" />
</form>
```

- アクション: `PATCH /users/1`
- ボタンラベル: "Update User"
- 既存の値が自動入力

## パラメータ名の規約

### ネストした構造

```erb
<%= form_with model: @user do |f| %>
  <%= f.text_field :name %>
<% end %>
```

送信されるパラメータ：

```ruby
{
  "user" => {
    "name" => "John"
  }
}
```

### コントローラでの受け取り

```ruby
class UsersController < ApplicationController
  def create
    @user = User.new(user_params)
    @user.save
  end

  private

  def user_params
    params.require(:user).permit(:name, :email)
  end
end
```

ポイント：
- `params.require(:user)` でモデル名のキーを要求
- `.permit(:name, :email)` で許可する属性を指定

## ネストしたリソースのフォーム

### 親子関係のあるフォーム

```erb
<%= form_with model: [@user, @post] do |f| %>
  <%= f.text_field :title %>
  <%= f.text_area :content %>
  <%= f.submit %>
<% end %>
```

自動推論される内容：

#### 新規作成

```html
<form action="/users/1/posts" method="post">
  <!-- フィールド -->
</form>
```

#### 更新

```html
<form action="/users/1/posts/2" method="post">
  <input type="hidden" name="_method" value="patch" />
  <!-- フィールド -->
</form>
```

## フィールドの種類

### テキスト入力

```erb
<%= f.text_field :name %>
<%= f.email_field :email %>
<%= f.password_field :password %>
<%= f.text_area :bio %>
<%= f.number_field :age %>
<%= f.url_field :website %>
<%= f.tel_field :phone %>
```

### 選択

```erb
<!-- セレクトボックス -->
<%= f.select :role, ['admin', 'user', 'guest'] %>
<%= f.select :country_id, Country.all.pluck(:name, :id) %>

<!-- ラジオボタン -->
<%= f.radio_button :gender, 'male' %>
<%= f.label :gender_male, 'Male' %>
<%= f.radio_button :gender, 'female' %>
<%= f.label :gender_female, 'Female' %>

<!-- チェックボックス -->
<%= f.check_box :terms_accepted %>
<%= f.label :terms_accepted, 'I accept the terms' %>
```

### 日付・時刻

```erb
<%= f.date_field :birth_date %>
<%= f.datetime_field :published_at %>
<%= f.time_field :start_time %>
```

### ファイルアップロード

```erb
<%= f.file_field :avatar %>
```

## エラーメッセージの表示

### バリデーションエラー

```erb
<%= form_with model: @user do |f| %>
  <% if @user.errors.any? %>
    <div id="error_explanation">
      <h2><%= pluralize(@user.errors.count, "error") %> prohibited this user from being saved:</h2>
      <ul>
        <% @user.errors.full_messages.each do |message| %>
          <li><%= message %></li>
        <% end %>
      </ul>
    </div>
  <% end %>

  <%= f.text_field :name %>
  <%= f.email_field :email %>
  <%= f.submit %>
<% end %>
```

### フィールドごとのエラー

```erb
<div class="field">
  <%= f.label :email %>
  <%= f.email_field :email %>
  <% if @user.errors[:email].any? %>
    <div class="error">
      <%= @user.errors[:email].join(', ') %>
    </div>
  <% end %>
</div>
```

## CSRF対策

### 自動挿入されるトークン

`form_with` は自動的にCSRF対策トークンを挿入します。

```html
<form action="/users" method="post">
  <input type="hidden" name="authenticity_token" value="..." />
  <!-- フィールド -->
</form>
```

このトークンにより、クロスサイトリクエストフォージェリ攻撃を防ぎます。

## コントローラでの処理

### 新規作成

```ruby
def new
  @user = User.new
end

def create
  @user = User.new(user_params)
  if @user.save
    redirect_to @user, notice: 'User was successfully created.'
  else
    render :new, status: :unprocessable_entity
  end
end
```

### 更新

```ruby
def edit
  @user = User.find(params[:id])
end

def update
  @user = User.find(params[:id])
  if @user.update(user_params)
    redirect_to @user, notice: 'User was successfully updated.'
  else
    render :edit, status: :unprocessable_entity
  end
end
```

## Strong Parameters

### 必須の仕組み

```ruby
private

def user_params
  params.require(:user).permit(:name, :email, :age)
end
```

ポイント：
- `require(:user)` でモデル名のキーを要求
- `permit(:name, :email)` でホワイトリスト方式で属性を許可
- セキュリティのため、すべての属性を自動許可しない

### ネストした属性

```ruby
def user_params
  params.require(:user).permit(
    :name,
    :email,
    profile_attributes: [:bio, :website],
    post_ids: []
  )
end
```

## フォームのカスタマイズ

### URLの明示的指定

```erb
<%= form_with model: @user, url: custom_path do |f| %>
  <%= f.text_field :name %>
  <%= f.submit %>
<% end %>
```

### HTTPメソッドの明示的指定

```erb
<%= form_with model: @user, method: :patch do |f| %>
  <%= f.text_field :name %>
  <%= f.submit %>
<% end %>
```

### ローカル送信（Ajax無効化）

```erb
<%= form_with model: @user, local: true do |f| %>
  <%= f.text_field :name %>
  <%= f.submit %>
<% end %>
```

## link_to での削除

### DELETEメソッド

```erb
<%= link_to "Delete", user_path(@user),
    method: :delete,
    data: { confirm: "Are you sure?" } %>
```

または

```erb
<%= button_to "Delete", @user, method: :delete,
    data: { confirm: "Are you sure?" } %>
```

## まとめ

- `form_with model: @user` で新規作成/更新を自動判定
- 新規: `POST /users`、更新: `PATCH /users/:id`
- パラメータは `user[name]` のようにネスト
- Strong Parameters で安全に属性を受け取る
- CSRF対策トークンが自動挿入
- エラーメッセージは `@user.errors` で取得
- `[@user, @post]` でネストしたリソースのフォームを作成
- 規約に従うことで、最小限のコードでフォームが機能
