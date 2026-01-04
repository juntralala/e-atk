select
    `skus`.*,
    (
        select
            sum(`transaction_items`.`base_quantity`)
        from
            `transaction_items`
        where
            `skus`.`id` = `transaction_items`.`sku_id`
            and exists (
                select
                    *
                from
                    `transactions`
                where
                    `transaction_items`.`transaction_id` = `transactions`.`id`
                    and `type` = ?
                    and `transactions`.`deleted_at` is null
            )
            and `transaction_items`.`deleted_at` is null
    ) as `total_quantity`,
    (
        select
            sum(base_quantity * price)
        from
            `transaction_items`
        where
            `skus`.`id` = `transaction_items`.`sku_id`
            and exists (
                select
                    *
                from
                    `transactions`
                where
                    `transaction_items`.`transaction_id` = `transactions`.`id`
                    and `type` = ?
                    and `transactions`.`deleted_at` is null
            )
            and `transaction_items`.`deleted_at` is null
    ) as `expenditure`,
    (
        select
            avg(`transaction_items`.`price`)
        from
            `transaction_items`
        where
            `skus`.`id` = `transaction_items`.`sku_id`
            and exists (
                select
                    *
                from
                    `transactions`
                where
                    `transaction_items`.`transaction_id` = `transactions`.`id`
                    and `type` = ?
                    and `transactions`.`deleted_at` is null
            )
            and `transaction_items`.`deleted_at` is null
    ) as `out_price`
from
    `skus`
where
    `skus`.`deleted_at` is null
limit
    10
offset
    0