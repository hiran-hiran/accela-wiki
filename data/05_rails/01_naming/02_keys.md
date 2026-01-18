---
title: 主キーと外部キー
description: 主キー（id）と外部キーの命名規約
---

# 主キーと外部キー

Railsでは、主キーと外部キーの命名規約が定められており、この規約に従うことで関連付けが自動的に機能します。

## 主キー

### デフォルトの主キー

テーブルの主キーは `id` カラムとして自動作成されます。

```ruby
# db/migrate/20240118000000_create_users.rb
class CreateUsers < ActiveRecord::Migration[7.0]
  def change
    create_table :users do |t|
      t.string :name
      t.string :email
      # id カラムが自動的に作成される
    end
  end
end
```

生成されるテーブル構造：

```sql
CREATE TABLE users (
  id BIGINT PRIMARY KEY AUTO_INCREMENT,
  name VARCHAR(255),
  email VARCHAR(255)
);
```

### 主キーの型

Rails 5.1以降、デフォルトで `BIGINT` 型が使用されます。

```ruby
User.create(name: "John")
# => #<User id: 1, name: "John", ...>

User.create(name: "Jane")
# => #<User id: 2, name: "Jane", ...>
```

### 主キーをカスタマイズ

規約と異なる主キーを使用する場合：

```ruby
class CreateProducts < ActiveRecord::Migration[7.0]
  def change
    create_table :products, primary_key: "product_code" do |t|
      t.string :name
    end
  end
end

class Product < ApplicationRecord
  self.primary_key = "product_code"
end
```

ただし、`id` を使う方が推奨されます。

## 外部キー

### 命名規約

外部キーは `[参照先テーブル名単数形]_id` という形式で命名します。

| テーブル | 外部キー | 参照先 |
|---------|---------|-------|
| `posts` | `user_id` | `users` テーブル |
| `comments` | `post_id` | `posts` テーブル |
| `order_items` | `order_id` | `orders` テーブル |
| `order_items` | `product_id` | `products` テーブル |

### マイグレーションでの定義

```ruby
# db/migrate/20240118000001_create_posts.rb
class CreatePosts < ActiveRecord::Migration[7.0]
  def change
    create_table :posts do |t|
      t.string :title
      t.text :content
      t.references :user, foreign_key: true  # user_id カラムを作成
      t.timestamps
    end
  end
end
```

`t.references` は以下と同等です：

```ruby
t.bigint :user_id
add_foreign_key :posts, :users
add_index :posts, :user_id
```

### 外部キーの自動参照

モデルでアソシエーションを定義すると、外部キーが自動参照されます。

```ruby
# app/models/post.rb
class Post < ApplicationRecord
  belongs_to :user  # user_id を自動的に使用
end

# app/models/user.rb
class User < ApplicationRecord
  has_many :posts   # posts テーブルの user_id を自動参照
end
```

### 使用例

```ruby
user = User.find(1)
post = user.posts.create(title: "Hello", content: "World")
# => #<Post id: 1, title: "Hello", content: "World", user_id: 1, ...>

post.user
# => #<User id: 1, name: "John", ...>
```

## 複合外部キー

複数の外部キーを持つ場合：

```ruby
# db/migrate/20240118000002_create_order_items.rb
class CreateOrderItems < ActiveRecord::Migration[7.0]
  def change
    create_table :order_items do |t|
      t.references :order, foreign_key: true   # order_id
      t.references :product, foreign_key: true # product_id
      t.integer :quantity
      t.timestamps
    end
  end
end
```

```ruby
# app/models/order_item.rb
class OrderItem < ApplicationRecord
  belongs_to :order
  belongs_to :product
end
```

## ポリモーフィック関連

複数のモデルに属する場合は、`_type` と `_id` の組み合わせを使用します。

```ruby
# db/migrate/20240118000003_create_comments.rb
class CreateComments < ActiveRecord::Migration[7.0]
  def change
    create_table :comments do |t|
      t.text :body
      t.references :commentable, polymorphic: true
      # commentable_type (string) と commentable_id (bigint) が作成される
      t.timestamps
    end
  end
end
```

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

## 外部キー名のカスタマイズ

規約と異なる外部キー名を使用する場合：

```ruby
class Post < ApplicationRecord
  belongs_to :author, class_name: "User", foreign_key: "author_id"
end
```

この場合、`user_id` ではなく `author_id` カラムを使用します。

## まとめ

- 主キーは `id`（BIGINT型、自動採番）
- 外部キーは `[モデル名単数形]_id`（例: `user_id`, `post_id`）
- `t.references` を使うと外部キーとインデックスが自動生成
- ポリモーフィック関連は `_type` と `_id` を使用
- 規約に従うことで、アソシエーションが自動的に機能
