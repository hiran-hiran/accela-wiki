---
title: アソシエーション
description: モデル間の関連付けと命名規約
---

# アソシエーション

Railsのアソシエーションは、命名規約に従うことで関連付けが自動的に推論されます。

## belongs_to（1対多の「多」側）

### 基本形

```ruby
class Post < ApplicationRecord
  belongs_to :user
end
```

この宣言により、以下が自動的に設定されます：

- 外部キー: `user_id`
- 参照先モデル: `User`
- 参照先テーブル: `users`

### 使用例

```ruby
post = Post.find(1)
post.user           # => User オブジェクトを取得
post.user_id        # => 外部キーの値を取得
post.user_id = 2    # => 外部キーを変更
post.build_user     # => 新しい User を作成（未保存）
post.create_user    # => 新しい User を作成して保存
```

## has_many（1対多の「1」側）

### 基本形

```ruby
class User < ApplicationRecord
  has_many :posts
end
```

この宣言により、以下が自動的に設定されます：

- 参照元テーブル: `posts`
- 外部キー: `user_id`（posts テーブル内）

### 使用例

```ruby
user = User.find(1)
user.posts                    # => Post の配列
user.posts.count              # => 投稿数
user.posts.create(title: "Hello")  # => 新しい投稿を作成
user.posts << Post.new        # => 既存の投稿を追加
user.posts.destroy_all        # => すべての投稿を削除
```

## has_one（1対1）

### 基本形

```ruby
class User < ApplicationRecord
  has_one :profile
end

class Profile < ApplicationRecord
  belongs_to :user
end
```

`profiles` テーブルに `user_id` が存在します。

### 使用例

```ruby
user = User.find(1)
user.profile              # => Profile オブジェクト
user.create_profile(bio: "Hello")  # => 新しいプロフィールを作成
user.build_profile        # => 新しいプロフィールを作成（未保存）
```

## has_many :through（多対多）

### 基本形

中間テーブルを経由した多対多の関連付け。

```ruby
class User < ApplicationRecord
  has_many :memberships
  has_many :groups, through: :memberships
end

class Membership < ApplicationRecord
  belongs_to :user
  belongs_to :group
end

class Group < ApplicationRecord
  has_many :memberships
  has_many :users, through: :memberships
end
```

### テーブル構造

```
users テーブル
- id

groups テーブル
- id

memberships テーブル（中間テーブル）
- id
- user_id
- group_id
```

### 使用例

```ruby
user = User.find(1)
user.groups               # => 所属グループの配列
user.groups << Group.find(1)  # => グループに参加

group = Group.find(1)
group.users               # => グループのメンバー一覧
```

## has_and_belongs_to_many（多対多・簡易版）

### 基本形

中間モデルが不要な場合に使用します。

```ruby
class User < ApplicationRecord
  has_and_belongs_to_many :roles
end

class Role < ApplicationRecord
  has_and_belongs_to_many :users
end
```

### 結合テーブルの命名規約

結合テーブル名は**アルファベット順**で結合します。

| モデル1 | モデル2 | 結合テーブル名 |
|---------|---------|--------------|
| `User` | `Role` | `roles_users` |
| `Post` | `Tag` | `posts_tags` |
| `Article` | `Category` | `articles_categories` |

### テーブル構造

```ruby
# db/migrate/20240118000000_create_roles_users.rb
class CreateRolesUsers < ActiveRecord::Migration[7.0]
  def change
    create_table :roles_users, id: false do |t|
      t.belongs_to :role
      t.belongs_to :user
    end
  end
end
```

注: `id: false` で主キーを作成しません。

### 使用例

```ruby
user = User.find(1)
user.roles << Role.find(1)  # => ロールを追加
user.roles                  # => ロールの配列
```

## ポリモーフィック関連

### 基本形

複数のモデルに属する場合に使用します。

```ruby
class Comment < ApplicationRecord
  belongs_to :commentable, polymorphic: true
end

class Post < ApplicationRecord
  has_many :comments, as: :commentable
end

class Video < ApplicationRecord
  has_many :comments, as: :commentable
end
```

### テーブル構造

```
comments テーブル
- id
- body
- commentable_type  # "Post" または "Video"
- commentable_id    # 対象のレコードID
```

### 使用例

```ruby
post = Post.find(1)
post.comments.create(body: "Great post!")

video = Video.find(1)
video.comments.create(body: "Nice video!")

comment = Comment.first
comment.commentable  # => Post または Video オブジェクト
```

## 自己結合

### 基本形

同じテーブル内での関連付け。

```ruby
class User < ApplicationRecord
  has_many :friendships
  has_many :friends, through: :friendships
end

class Friendship < ApplicationRecord
  belongs_to :user
  belongs_to :friend, class_name: "User"
end
```

### 使用例

```ruby
user = User.find(1)
user.friends << User.find(2)
user.friends  # => フレンドの配列
```

## 関連名のカスタマイズ

規約と異なる名前を使用する場合：

```ruby
class Post < ApplicationRecord
  belongs_to :author, class_name: "User", foreign_key: "author_id"
end

class User < ApplicationRecord
  has_many :authored_posts, class_name: "Post", foreign_key: "author_id"
end
```

```ruby
post.author           # => User オブジェクト
user.authored_posts   # => Post の配列
```

## 依存関係の設定

### dependent オプション

```ruby
class User < ApplicationRecord
  has_many :posts, dependent: :destroy
  # ユーザーを削除すると、関連する投稿も削除される
end
```

| オプション | 動作 |
|-----------|------|
| `:destroy` | 関連レコードも削除（コールバック実行） |
| `:delete_all` | 関連レコードも削除（コールバック不実行） |
| `:nullify` | 外部キーをNULLに設定 |
| `:restrict_with_exception` | 関連レコードがあれば例外発生 |
| `:restrict_with_error` | 関連レコードがあればエラー追加 |

## まとめ

- `belongs_to` → 外部キーは `[関連名]_id`
- `has_many` → 関連先の外部キーを自動推論
- `has_many :through` → 中間モデル経由の多対多
- `has_and_belongs_to_many` → 結合テーブル名はアルファベット順
- ポリモーフィック → `_type` と `_id` を使用
- 規約に従うことで、すべて自動的に機能
