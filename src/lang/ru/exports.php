<?php

declare(strict_types=1);

return [
    'products' => [
        'id' => 'ID',
        'name' => 'Название',
        'description' => 'Описание',
        'price' => 'Цена',
        'quantity' => 'Количество',
        'status' => 'Статус',
        'owner' => 'Владелец',
        'created_at' => 'Дата создания',
    ],

    'product_reviews' => [
        'id' => 'ID',
        'product' => 'Товар',
        'user' => 'Пользователь',
        'rating' => 'Оценка',
        'comment' => 'Комментарий',
        'created_at' => 'Дата создания',
    ],

    'users' => [
        'id' => 'ID',
        'name' => 'Имя',
        'email' => 'Email',
        'is_admin' => 'Администратор',
        'reviews_count' => 'Количество отзывов',
        'created_at' => 'Дата регистрации',
    ],

    'common' => [
        'yes' => 'Да',
        'no' => 'Нет',
        'empty' => '-',
    ],
];
