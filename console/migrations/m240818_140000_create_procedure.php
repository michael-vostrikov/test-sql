<?php

class m240818_140000_create_procedure extends yii\db\Migration
{
    public function safeUp()
    {
        $sql = <<<'SQL'
            CREATE TYPE details_compose_tp AS (
              sku   integer,
              price decimal(13,2),
              qty   integer
            );
        SQL;
        $this->execute($sql);

        $sql = <<<'SQL'
            CREATE TYPE order_compose_tp AS (
              external_id   varchar,
              order_num     varchar,
              order_date    date,
              order_details details_compose_tp[]
            );
        SQL;
        $this->execute($sql);

        $sql = <<<'SQL'
            CREATE OR REPLACE FUNCTION save_order (
              customer_order order_compose_tp
            ) RETURNS varchar AS $function$
            
              WITH OrderNum AS (
              
                INSERT INTO customer AS C (external_id, last_order)
                SELECT
                  (customer_order).external_id,
                  
                  (customer_order).external_id
                    || EXTRACT(YEAR FROM transaction_timestamp())::text
                    ||'/000001'
                    
                WHERE (customer_order).order_num IS NULL
                
                ON CONFLICT (external_id) DO
                UPDATE SET
                  last_order = C.external_id
                    || EXTRACT(YEAR FROM transaction_timestamp())::text
                    || '/'
                    || RIGHT('00000' || (
                        CASE WHEN regexp_substr(C.last_order, '\d{4}(?=/)')::integer = EXTRACT(YEAR FROM transaction_timestamp())
                        THEN
                          COALESCE(
                            regexp_substr(C.last_order, '(?<=/)\d+'),
                            '0'
                          )::integer + 1
                        ELSE '000001' END
                      )::text, 6
                    )
                RETURNING last_order
              ),
              
              Header AS (
                INSERT INTO order_header (order_num, customer_id, order_date)
                SELECT COALESCE(
                    (customer_order).order_num,
                    O.last_order
                  ),
                  C.id,
                  (customer_order).order_date
                FROM customer C
                LEFT JOIN OrderNum O ON true
                
                WHERE C.external_id = (customer_order).external_id
                ON CONFLICT (order_num) DO
                UPDATE SET customer_id = EXCLUDED.customer_id,
                  order_date = EXCLUDED.order_date
                RETURNING id, order_num
              ),
              
              DelDets AS (
                DELETE FROM order_details AS D
                USING Header H
                WHERE (customer_order).order_num IS NOT NULL AND H.id=D.order_id
              ),
              
              Dets AS (
                INSERT INTO order_details
                  (order_id, line_num, sku, price, qty)
                SELECT H.id, D.line_num, D.sku, D.price, D.qty
                FROM Header H
                CROSS JOIN unnest((customer_order).order_details) WITH ORDINALITY
                  D(sku, price, qty, line_num)
              )
            
              SELECT H.order_num
              FROM Header H;
              
              
            $function$  LANGUAGE SQL;
        SQL;
        $this->execute($sql);
    }

    public function safeDown()
    {
        $this->execute("DROP FUNCTION save_order");
        $this->execute("DROP TYPE order_compose_tp");
        $this->execute("DROP TYPE details_compose_tp;");
    }
}
