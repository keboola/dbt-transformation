

-- We'll fake some data, since this is an example repository
-- we want to make sure that we continue to generate data up
-- to today

WITH generated_orders AS (
    
        
          SELECT
              CONCAT(
                   
    TO_VARCHAR(
                        DATEADD(Day, -1 * 0, CURRENT_DATE),
                        'YYYYMMDD'
                   ),
                   '0'
             )::int                                                            AS order_no,

             UNIFORM(123456, 654321, RANDOM())                                  AS customer_id,

             
                'PENDING'                                                      AS status,
             
             DATEADD(Day, -1 * 0, CURRENT_DATE)                    AS created_at,
             current_timestamp                                                 AS changed_at

          
            UNION ALL
          
        
          SELECT
              CONCAT(
                   
    TO_VARCHAR(
                        DATEADD(Day, -1 * 0, CURRENT_DATE),
                        'YYYYMMDD'
                   ),
                   '1'
             )::int                                                            AS order_no,

             UNIFORM(123456, 654321, RANDOM())                                  AS customer_id,

             
                'DELIVERED'                                                    AS status,
             
             DATEADD(Day, -1 * 0, CURRENT_DATE)                    AS created_at,
             current_timestamp                                                 AS changed_at

          
            UNION ALL
          
        
          SELECT
              CONCAT(
                   
    TO_VARCHAR(
                        DATEADD(Day, -1 * 0, CURRENT_DATE),
                        'YYYYMMDD'
                   ),
                   '2'
             )::int                                                            AS order_no,

             UNIFORM(123456, 654321, RANDOM())                                  AS customer_id,

             
                'DELIVERED'                                                    AS status,
             
             DATEADD(Day, -1 * 0, CURRENT_DATE)                    AS created_at,
             current_timestamp                                                 AS changed_at

          
            UNION ALL
          
        
          SELECT
              CONCAT(
                   
    TO_VARCHAR(
                        DATEADD(Day, -1 * 0, CURRENT_DATE),
                        'YYYYMMDD'
                   ),
                   '3'
             )::int                                                            AS order_no,

             UNIFORM(123456, 654321, RANDOM())                                  AS customer_id,

             
                'DELIVERED'                                                    AS status,
             
             DATEADD(Day, -1 * 0, CURRENT_DATE)                    AS created_at,
             current_timestamp                                                 AS changed_at

          
            UNION ALL
          
        
          SELECT
              CONCAT(
                   
    TO_VARCHAR(
                        DATEADD(Day, -1 * 0, CURRENT_DATE),
                        'YYYYMMDD'
                   ),
                   '4'
             )::int                                                            AS order_no,

             UNIFORM(123456, 654321, RANDOM())                                  AS customer_id,

             
                'DELIVERED'                                                    AS status,
             
             DATEADD(Day, -1 * 0, CURRENT_DATE)                    AS created_at,
             current_timestamp                                                 AS changed_at

          
            UNION ALL
          
        
          SELECT
              CONCAT(
                   
    TO_VARCHAR(
                        DATEADD(Day, -1 * 0, CURRENT_DATE),
                        'YYYYMMDD'
                   ),
                   '5'
             )::int                                                            AS order_no,

             UNIFORM(123456, 654321, RANDOM())                                  AS customer_id,

             
                'DELIVERED'                                                    AS status,
             
             DATEADD(Day, -1 * 0, CURRENT_DATE)                    AS created_at,
             current_timestamp                                                 AS changed_at

          
            UNION ALL
          
        
          SELECT
              CONCAT(
                   
    TO_VARCHAR(
                        DATEADD(Day, -1 * 0, CURRENT_DATE),
                        'YYYYMMDD'
                   ),
                   '6'
             )::int                                                            AS order_no,

             UNIFORM(123456, 654321, RANDOM())                                  AS customer_id,

             
                'DELIVERED'                                                    AS status,
             
             DATEADD(Day, -1 * 0, CURRENT_DATE)                    AS created_at,
             current_timestamp                                                 AS changed_at

          
            UNION ALL
          
        
          SELECT
              CONCAT(
                   
    TO_VARCHAR(
                        DATEADD(Day, -1 * 0, CURRENT_DATE),
                        'YYYYMMDD'
                   ),
                   '7'
             )::int                                                            AS order_no,

             UNIFORM(123456, 654321, RANDOM())                                  AS customer_id,

             
                'DELIVERED'                                                    AS status,
             
             DATEADD(Day, -1 * 0, CURRENT_DATE)                    AS created_at,
             current_timestamp                                                 AS changed_at

          
            UNION ALL
          
        
          SELECT
              CONCAT(
                   
    TO_VARCHAR(
                        DATEADD(Day, -1 * 0, CURRENT_DATE),
                        'YYYYMMDD'
                   ),
                   '8'
             )::int                                                            AS order_no,

             UNIFORM(123456, 654321, RANDOM())                                  AS customer_id,

             
                'DELIVERED'                                                    AS status,
             
             DATEADD(Day, -1 * 0, CURRENT_DATE)                    AS created_at,
             current_timestamp                                                 AS changed_at

          
            UNION ALL
          
        
          SELECT
              CONCAT(
                   
    TO_VARCHAR(
                        DATEADD(Day, -1 * 0, CURRENT_DATE),
                        'YYYYMMDD'
                   ),
                   '9'
             )::int                                                            AS order_no,

             UNIFORM(123456, 654321, RANDOM())                                  AS customer_id,

             
                'DELIVERED'                                                    AS status,
             
             DATEADD(Day, -1 * 0, CURRENT_DATE)                    AS created_at,
             current_timestamp                                                 AS changed_at

          
        

        
            UNION ALL
        
    
        
          SELECT
              CONCAT(
                   
    TO_VARCHAR(
                        DATEADD(Day, -1 * 1, CURRENT_DATE),
                        'YYYYMMDD'
                   ),
                   '0'
             )::int                                                            AS order_no,

             UNIFORM(123456, 654321, RANDOM())                                  AS customer_id,

             
                'PENDING'                                                      AS status,
             
             DATEADD(Day, -1 * 1, CURRENT_DATE)                    AS created_at,
             current_timestamp                                                 AS changed_at

          
            UNION ALL
          
        
          SELECT
              CONCAT(
                   
    TO_VARCHAR(
                        DATEADD(Day, -1 * 1, CURRENT_DATE),
                        'YYYYMMDD'
                   ),
                   '1'
             )::int                                                            AS order_no,

             UNIFORM(123456, 654321, RANDOM())                                  AS customer_id,

             
                'DELIVERED'                                                    AS status,
             
             DATEADD(Day, -1 * 1, CURRENT_DATE)                    AS created_at,
             current_timestamp                                                 AS changed_at

          
            UNION ALL
          
        
          SELECT
              CONCAT(
                   
    TO_VARCHAR(
                        DATEADD(Day, -1 * 1, CURRENT_DATE),
                        'YYYYMMDD'
                   ),
                   '2'
             )::int                                                            AS order_no,

             UNIFORM(123456, 654321, RANDOM())                                  AS customer_id,

             
                'DELIVERED'                                                    AS status,
             
             DATEADD(Day, -1 * 1, CURRENT_DATE)                    AS created_at,
             current_timestamp                                                 AS changed_at

          
            UNION ALL
          
        
          SELECT
              CONCAT(
                   
    TO_VARCHAR(
                        DATEADD(Day, -1 * 1, CURRENT_DATE),
                        'YYYYMMDD'
                   ),
                   '3'
             )::int                                                            AS order_no,

             UNIFORM(123456, 654321, RANDOM())                                  AS customer_id,

             
                'DELIVERED'                                                    AS status,
             
             DATEADD(Day, -1 * 1, CURRENT_DATE)                    AS created_at,
             current_timestamp                                                 AS changed_at

          
            UNION ALL
          
        
          SELECT
              CONCAT(
                   
    TO_VARCHAR(
                        DATEADD(Day, -1 * 1, CURRENT_DATE),
                        'YYYYMMDD'
                   ),
                   '4'
             )::int                                                            AS order_no,

             UNIFORM(123456, 654321, RANDOM())                                  AS customer_id,

             
                'DELIVERED'                                                    AS status,
             
             DATEADD(Day, -1 * 1, CURRENT_DATE)                    AS created_at,
             current_timestamp                                                 AS changed_at

          
            UNION ALL
          
        
          SELECT
              CONCAT(
                   
    TO_VARCHAR(
                        DATEADD(Day, -1 * 1, CURRENT_DATE),
                        'YYYYMMDD'
                   ),
                   '5'
             )::int                                                            AS order_no,

             UNIFORM(123456, 654321, RANDOM())                                  AS customer_id,

             
                'DELIVERED'                                                    AS status,
             
             DATEADD(Day, -1 * 1, CURRENT_DATE)                    AS created_at,
             current_timestamp                                                 AS changed_at

          
            UNION ALL
          
        
          SELECT
              CONCAT(
                   
    TO_VARCHAR(
                        DATEADD(Day, -1 * 1, CURRENT_DATE),
                        'YYYYMMDD'
                   ),
                   '6'
             )::int                                                            AS order_no,

             UNIFORM(123456, 654321, RANDOM())                                  AS customer_id,

             
                'DELIVERED'                                                    AS status,
             
             DATEADD(Day, -1 * 1, CURRENT_DATE)                    AS created_at,
             current_timestamp                                                 AS changed_at

          
            UNION ALL
          
        
          SELECT
              CONCAT(
                   
    TO_VARCHAR(
                        DATEADD(Day, -1 * 1, CURRENT_DATE),
                        'YYYYMMDD'
                   ),
                   '7'
             )::int                                                            AS order_no,

             UNIFORM(123456, 654321, RANDOM())                                  AS customer_id,

             
                'DELIVERED'                                                    AS status,
             
             DATEADD(Day, -1 * 1, CURRENT_DATE)                    AS created_at,
             current_timestamp                                                 AS changed_at

          
            UNION ALL
          
        
          SELECT
              CONCAT(
                   
    TO_VARCHAR(
                        DATEADD(Day, -1 * 1, CURRENT_DATE),
                        'YYYYMMDD'
                   ),
                   '8'
             )::int                                                            AS order_no,

             UNIFORM(123456, 654321, RANDOM())                                  AS customer_id,

             
                'DELIVERED'                                                    AS status,
             
             DATEADD(Day, -1 * 1, CURRENT_DATE)                    AS created_at,
             current_timestamp                                                 AS changed_at

          
            UNION ALL
          
        
          SELECT
              CONCAT(
                   
    TO_VARCHAR(
                        DATEADD(Day, -1 * 1, CURRENT_DATE),
                        'YYYYMMDD'
                   ),
                   '9'
             )::int                                                            AS order_no,

             UNIFORM(123456, 654321, RANDOM())                                  AS customer_id,

             
                'DELIVERED'                                                    AS status,
             
             DATEADD(Day, -1 * 1, CURRENT_DATE)                    AS created_at,
             current_timestamp                                                 AS changed_at

          
        

        
            UNION ALL
        
    
        
          SELECT
              CONCAT(
                   
    TO_VARCHAR(
                        DATEADD(Day, -1 * 2, CURRENT_DATE),
                        'YYYYMMDD'
                   ),
                   '0'
             )::int                                                            AS order_no,

             UNIFORM(123456, 654321, RANDOM())                                  AS customer_id,

             
                'PENDING'                                                      AS status,
             
             DATEADD(Day, -1 * 2, CURRENT_DATE)                    AS created_at,
             current_timestamp                                                 AS changed_at

          
            UNION ALL
          
        
          SELECT
              CONCAT(
                   
    TO_VARCHAR(
                        DATEADD(Day, -1 * 2, CURRENT_DATE),
                        'YYYYMMDD'
                   ),
                   '1'
             )::int                                                            AS order_no,

             UNIFORM(123456, 654321, RANDOM())                                  AS customer_id,

             
                'DELIVERED'                                                    AS status,
             
             DATEADD(Day, -1 * 2, CURRENT_DATE)                    AS created_at,
             current_timestamp                                                 AS changed_at

          
            UNION ALL
          
        
          SELECT
              CONCAT(
                   
    TO_VARCHAR(
                        DATEADD(Day, -1 * 2, CURRENT_DATE),
                        'YYYYMMDD'
                   ),
                   '2'
             )::int                                                            AS order_no,

             UNIFORM(123456, 654321, RANDOM())                                  AS customer_id,

             
                'DELIVERED'                                                    AS status,
             
             DATEADD(Day, -1 * 2, CURRENT_DATE)                    AS created_at,
             current_timestamp                                                 AS changed_at

          
            UNION ALL
          
        
          SELECT
              CONCAT(
                   
    TO_VARCHAR(
                        DATEADD(Day, -1 * 2, CURRENT_DATE),
                        'YYYYMMDD'
                   ),
                   '3'
             )::int                                                            AS order_no,

             UNIFORM(123456, 654321, RANDOM())                                  AS customer_id,

             
                'DELIVERED'                                                    AS status,
             
             DATEADD(Day, -1 * 2, CURRENT_DATE)                    AS created_at,
             current_timestamp                                                 AS changed_at

          
            UNION ALL
          
        
          SELECT
              CONCAT(
                   
    TO_VARCHAR(
                        DATEADD(Day, -1 * 2, CURRENT_DATE),
                        'YYYYMMDD'
                   ),
                   '4'
             )::int                                                            AS order_no,

             UNIFORM(123456, 654321, RANDOM())                                  AS customer_id,

             
                'DELIVERED'                                                    AS status,
             
             DATEADD(Day, -1 * 2, CURRENT_DATE)                    AS created_at,
             current_timestamp                                                 AS changed_at

          
            UNION ALL
          
        
          SELECT
              CONCAT(
                   
    TO_VARCHAR(
                        DATEADD(Day, -1 * 2, CURRENT_DATE),
                        'YYYYMMDD'
                   ),
                   '5'
             )::int                                                            AS order_no,

             UNIFORM(123456, 654321, RANDOM())                                  AS customer_id,

             
                'DELIVERED'                                                    AS status,
             
             DATEADD(Day, -1 * 2, CURRENT_DATE)                    AS created_at,
             current_timestamp                                                 AS changed_at

          
            UNION ALL
          
        
          SELECT
              CONCAT(
                   
    TO_VARCHAR(
                        DATEADD(Day, -1 * 2, CURRENT_DATE),
                        'YYYYMMDD'
                   ),
                   '6'
             )::int                                                            AS order_no,

             UNIFORM(123456, 654321, RANDOM())                                  AS customer_id,

             
                'DELIVERED'                                                    AS status,
             
             DATEADD(Day, -1 * 2, CURRENT_DATE)                    AS created_at,
             current_timestamp                                                 AS changed_at

          
            UNION ALL
          
        
          SELECT
              CONCAT(
                   
    TO_VARCHAR(
                        DATEADD(Day, -1 * 2, CURRENT_DATE),
                        'YYYYMMDD'
                   ),
                   '7'
             )::int                                                            AS order_no,

             UNIFORM(123456, 654321, RANDOM())                                  AS customer_id,

             
                'DELIVERED'                                                    AS status,
             
             DATEADD(Day, -1 * 2, CURRENT_DATE)                    AS created_at,
             current_timestamp                                                 AS changed_at

          
            UNION ALL
          
        
          SELECT
              CONCAT(
                   
    TO_VARCHAR(
                        DATEADD(Day, -1 * 2, CURRENT_DATE),
                        'YYYYMMDD'
                   ),
                   '8'
             )::int                                                            AS order_no,

             UNIFORM(123456, 654321, RANDOM())                                  AS customer_id,

             
                'DELIVERED'                                                    AS status,
             
             DATEADD(Day, -1 * 2, CURRENT_DATE)                    AS created_at,
             current_timestamp                                                 AS changed_at

          
            UNION ALL
          
        
          SELECT
              CONCAT(
                   
    TO_VARCHAR(
                        DATEADD(Day, -1 * 2, CURRENT_DATE),
                        'YYYYMMDD'
                   ),
                   '9'
             )::int                                                            AS order_no,

             UNIFORM(123456, 654321, RANDOM())                                  AS customer_id,

             
                'DELIVERED'                                                    AS status,
             
             DATEADD(Day, -1 * 2, CURRENT_DATE)                    AS created_at,
             current_timestamp                                                 AS changed_at

          
        

        
            UNION ALL
        
    
        
          SELECT
              CONCAT(
                   
    TO_VARCHAR(
                        DATEADD(Day, -1 * 3, CURRENT_DATE),
                        'YYYYMMDD'
                   ),
                   '0'
             )::int                                                            AS order_no,

             UNIFORM(123456, 654321, RANDOM())                                  AS customer_id,

             
                'PENDING'                                                      AS status,
             
             DATEADD(Day, -1 * 3, CURRENT_DATE)                    AS created_at,
             current_timestamp                                                 AS changed_at

          
            UNION ALL
          
        
          SELECT
              CONCAT(
                   
    TO_VARCHAR(
                        DATEADD(Day, -1 * 3, CURRENT_DATE),
                        'YYYYMMDD'
                   ),
                   '1'
             )::int                                                            AS order_no,

             UNIFORM(123456, 654321, RANDOM())                                  AS customer_id,

             
                'DELIVERED'                                                    AS status,
             
             DATEADD(Day, -1 * 3, CURRENT_DATE)                    AS created_at,
             current_timestamp                                                 AS changed_at

          
            UNION ALL
          
        
          SELECT
              CONCAT(
                   
    TO_VARCHAR(
                        DATEADD(Day, -1 * 3, CURRENT_DATE),
                        'YYYYMMDD'
                   ),
                   '2'
             )::int                                                            AS order_no,

             UNIFORM(123456, 654321, RANDOM())                                  AS customer_id,

             
                'DELIVERED'                                                    AS status,
             
             DATEADD(Day, -1 * 3, CURRENT_DATE)                    AS created_at,
             current_timestamp                                                 AS changed_at

          
            UNION ALL
          
        
          SELECT
              CONCAT(
                   
    TO_VARCHAR(
                        DATEADD(Day, -1 * 3, CURRENT_DATE),
                        'YYYYMMDD'
                   ),
                   '3'
             )::int                                                            AS order_no,

             UNIFORM(123456, 654321, RANDOM())                                  AS customer_id,

             
                'DELIVERED'                                                    AS status,
             
             DATEADD(Day, -1 * 3, CURRENT_DATE)                    AS created_at,
             current_timestamp                                                 AS changed_at

          
            UNION ALL
          
        
          SELECT
              CONCAT(
                   
    TO_VARCHAR(
                        DATEADD(Day, -1 * 3, CURRENT_DATE),
                        'YYYYMMDD'
                   ),
                   '4'
             )::int                                                            AS order_no,

             UNIFORM(123456, 654321, RANDOM())                                  AS customer_id,

             
                'DELIVERED'                                                    AS status,
             
             DATEADD(Day, -1 * 3, CURRENT_DATE)                    AS created_at,
             current_timestamp                                                 AS changed_at

          
            UNION ALL
          
        
          SELECT
              CONCAT(
                   
    TO_VARCHAR(
                        DATEADD(Day, -1 * 3, CURRENT_DATE),
                        'YYYYMMDD'
                   ),
                   '5'
             )::int                                                            AS order_no,

             UNIFORM(123456, 654321, RANDOM())                                  AS customer_id,

             
                'DELIVERED'                                                    AS status,
             
             DATEADD(Day, -1 * 3, CURRENT_DATE)                    AS created_at,
             current_timestamp                                                 AS changed_at

          
            UNION ALL
          
        
          SELECT
              CONCAT(
                   
    TO_VARCHAR(
                        DATEADD(Day, -1 * 3, CURRENT_DATE),
                        'YYYYMMDD'
                   ),
                   '6'
             )::int                                                            AS order_no,

             UNIFORM(123456, 654321, RANDOM())                                  AS customer_id,

             
                'DELIVERED'                                                    AS status,
             
             DATEADD(Day, -1 * 3, CURRENT_DATE)                    AS created_at,
             current_timestamp                                                 AS changed_at

          
            UNION ALL
          
        
          SELECT
              CONCAT(
                   
    TO_VARCHAR(
                        DATEADD(Day, -1 * 3, CURRENT_DATE),
                        'YYYYMMDD'
                   ),
                   '7'
             )::int                                                            AS order_no,

             UNIFORM(123456, 654321, RANDOM())                                  AS customer_id,

             
                'DELIVERED'                                                    AS status,
             
             DATEADD(Day, -1 * 3, CURRENT_DATE)                    AS created_at,
             current_timestamp                                                 AS changed_at

          
            UNION ALL
          
        
          SELECT
              CONCAT(
                   
    TO_VARCHAR(
                        DATEADD(Day, -1 * 3, CURRENT_DATE),
                        'YYYYMMDD'
                   ),
                   '8'
             )::int                                                            AS order_no,

             UNIFORM(123456, 654321, RANDOM())                                  AS customer_id,

             
                'DELIVERED'                                                    AS status,
             
             DATEADD(Day, -1 * 3, CURRENT_DATE)                    AS created_at,
             current_timestamp                                                 AS changed_at

          
            UNION ALL
          
        
          SELECT
              CONCAT(
                   
    TO_VARCHAR(
                        DATEADD(Day, -1 * 3, CURRENT_DATE),
                        'YYYYMMDD'
                   ),
                   '9'
             )::int                                                            AS order_no,

             UNIFORM(123456, 654321, RANDOM())                                  AS customer_id,

             
                'DELIVERED'                                                    AS status,
             
             DATEADD(Day, -1 * 3, CURRENT_DATE)                    AS created_at,
             current_timestamp                                                 AS changed_at

          
        

        
            UNION ALL
        
    
        
          SELECT
              CONCAT(
                   
    TO_VARCHAR(
                        DATEADD(Day, -1 * 4, CURRENT_DATE),
                        'YYYYMMDD'
                   ),
                   '0'
             )::int                                                            AS order_no,

             UNIFORM(123456, 654321, RANDOM())                                  AS customer_id,

             
                'PENDING'                                                      AS status,
             
             DATEADD(Day, -1 * 4, CURRENT_DATE)                    AS created_at,
             current_timestamp                                                 AS changed_at

          
            UNION ALL
          
        
          SELECT
              CONCAT(
                   
    TO_VARCHAR(
                        DATEADD(Day, -1 * 4, CURRENT_DATE),
                        'YYYYMMDD'
                   ),
                   '1'
             )::int                                                            AS order_no,

             UNIFORM(123456, 654321, RANDOM())                                  AS customer_id,

             
                'DELIVERED'                                                    AS status,
             
             DATEADD(Day, -1 * 4, CURRENT_DATE)                    AS created_at,
             current_timestamp                                                 AS changed_at

          
            UNION ALL
          
        
          SELECT
              CONCAT(
                   
    TO_VARCHAR(
                        DATEADD(Day, -1 * 4, CURRENT_DATE),
                        'YYYYMMDD'
                   ),
                   '2'
             )::int                                                            AS order_no,

             UNIFORM(123456, 654321, RANDOM())                                  AS customer_id,

             
                'DELIVERED'                                                    AS status,
             
             DATEADD(Day, -1 * 4, CURRENT_DATE)                    AS created_at,
             current_timestamp                                                 AS changed_at

          
            UNION ALL
          
        
          SELECT
              CONCAT(
                   
    TO_VARCHAR(
                        DATEADD(Day, -1 * 4, CURRENT_DATE),
                        'YYYYMMDD'
                   ),
                   '3'
             )::int                                                            AS order_no,

             UNIFORM(123456, 654321, RANDOM())                                  AS customer_id,

             
                'DELIVERED'                                                    AS status,
             
             DATEADD(Day, -1 * 4, CURRENT_DATE)                    AS created_at,
             current_timestamp                                                 AS changed_at

          
            UNION ALL
          
        
          SELECT
              CONCAT(
                   
    TO_VARCHAR(
                        DATEADD(Day, -1 * 4, CURRENT_DATE),
                        'YYYYMMDD'
                   ),
                   '4'
             )::int                                                            AS order_no,

             UNIFORM(123456, 654321, RANDOM())                                  AS customer_id,

             
                'DELIVERED'                                                    AS status,
             
             DATEADD(Day, -1 * 4, CURRENT_DATE)                    AS created_at,
             current_timestamp                                                 AS changed_at

          
            UNION ALL
          
        
          SELECT
              CONCAT(
                   
    TO_VARCHAR(
                        DATEADD(Day, -1 * 4, CURRENT_DATE),
                        'YYYYMMDD'
                   ),
                   '5'
             )::int                                                            AS order_no,

             UNIFORM(123456, 654321, RANDOM())                                  AS customer_id,

             
                'DELIVERED'                                                    AS status,
             
             DATEADD(Day, -1 * 4, CURRENT_DATE)                    AS created_at,
             current_timestamp                                                 AS changed_at

          
            UNION ALL
          
        
          SELECT
              CONCAT(
                   
    TO_VARCHAR(
                        DATEADD(Day, -1 * 4, CURRENT_DATE),
                        'YYYYMMDD'
                   ),
                   '6'
             )::int                                                            AS order_no,

             UNIFORM(123456, 654321, RANDOM())                                  AS customer_id,

             
                'DELIVERED'                                                    AS status,
             
             DATEADD(Day, -1 * 4, CURRENT_DATE)                    AS created_at,
             current_timestamp                                                 AS changed_at

          
            UNION ALL
          
        
          SELECT
              CONCAT(
                   
    TO_VARCHAR(
                        DATEADD(Day, -1 * 4, CURRENT_DATE),
                        'YYYYMMDD'
                   ),
                   '7'
             )::int                                                            AS order_no,

             UNIFORM(123456, 654321, RANDOM())                                  AS customer_id,

             
                'DELIVERED'                                                    AS status,
             
             DATEADD(Day, -1 * 4, CURRENT_DATE)                    AS created_at,
             current_timestamp                                                 AS changed_at

          
            UNION ALL
          
        
          SELECT
              CONCAT(
                   
    TO_VARCHAR(
                        DATEADD(Day, -1 * 4, CURRENT_DATE),
                        'YYYYMMDD'
                   ),
                   '8'
             )::int                                                            AS order_no,

             UNIFORM(123456, 654321, RANDOM())                                  AS customer_id,

             
                'DELIVERED'                                                    AS status,
             
             DATEADD(Day, -1 * 4, CURRENT_DATE)                    AS created_at,
             current_timestamp                                                 AS changed_at

          
            UNION ALL
          
        
          SELECT
              CONCAT(
                   
    TO_VARCHAR(
                        DATEADD(Day, -1 * 4, CURRENT_DATE),
                        'YYYYMMDD'
                   ),
                   '9'
             )::int                                                            AS order_no,

             UNIFORM(123456, 654321, RANDOM())                                  AS customer_id,

             
                'DELIVERED'                                                    AS status,
             
             DATEADD(Day, -1 * 4, CURRENT_DATE)                    AS created_at,
             current_timestamp                                                 AS changed_at

          
        

        
            UNION ALL
        
    
        
          SELECT
              CONCAT(
                   
    TO_VARCHAR(
                        DATEADD(Day, -1 * 5, CURRENT_DATE),
                        'YYYYMMDD'
                   ),
                   '0'
             )::int                                                            AS order_no,

             UNIFORM(123456, 654321, RANDOM())                                  AS customer_id,

             
                'PENDING'                                                      AS status,
             
             DATEADD(Day, -1 * 5, CURRENT_DATE)                    AS created_at,
             current_timestamp                                                 AS changed_at

          
            UNION ALL
          
        
          SELECT
              CONCAT(
                   
    TO_VARCHAR(
                        DATEADD(Day, -1 * 5, CURRENT_DATE),
                        'YYYYMMDD'
                   ),
                   '1'
             )::int                                                            AS order_no,

             UNIFORM(123456, 654321, RANDOM())                                  AS customer_id,

             
                'DELIVERED'                                                    AS status,
             
             DATEADD(Day, -1 * 5, CURRENT_DATE)                    AS created_at,
             current_timestamp                                                 AS changed_at

          
            UNION ALL
          
        
          SELECT
              CONCAT(
                   
    TO_VARCHAR(
                        DATEADD(Day, -1 * 5, CURRENT_DATE),
                        'YYYYMMDD'
                   ),
                   '2'
             )::int                                                            AS order_no,

             UNIFORM(123456, 654321, RANDOM())                                  AS customer_id,

             
                'DELIVERED'                                                    AS status,
             
             DATEADD(Day, -1 * 5, CURRENT_DATE)                    AS created_at,
             current_timestamp                                                 AS changed_at

          
            UNION ALL
          
        
          SELECT
              CONCAT(
                   
    TO_VARCHAR(
                        DATEADD(Day, -1 * 5, CURRENT_DATE),
                        'YYYYMMDD'
                   ),
                   '3'
             )::int                                                            AS order_no,

             UNIFORM(123456, 654321, RANDOM())                                  AS customer_id,

             
                'DELIVERED'                                                    AS status,
             
             DATEADD(Day, -1 * 5, CURRENT_DATE)                    AS created_at,
             current_timestamp                                                 AS changed_at

          
            UNION ALL
          
        
          SELECT
              CONCAT(
                   
    TO_VARCHAR(
                        DATEADD(Day, -1 * 5, CURRENT_DATE),
                        'YYYYMMDD'
                   ),
                   '4'
             )::int                                                            AS order_no,

             UNIFORM(123456, 654321, RANDOM())                                  AS customer_id,

             
                'DELIVERED'                                                    AS status,
             
             DATEADD(Day, -1 * 5, CURRENT_DATE)                    AS created_at,
             current_timestamp                                                 AS changed_at

          
            UNION ALL
          
        
          SELECT
              CONCAT(
                   
    TO_VARCHAR(
                        DATEADD(Day, -1 * 5, CURRENT_DATE),
                        'YYYYMMDD'
                   ),
                   '5'
             )::int                                                            AS order_no,

             UNIFORM(123456, 654321, RANDOM())                                  AS customer_id,

             
                'DELIVERED'                                                    AS status,
             
             DATEADD(Day, -1 * 5, CURRENT_DATE)                    AS created_at,
             current_timestamp                                                 AS changed_at

          
            UNION ALL
          
        
          SELECT
              CONCAT(
                   
    TO_VARCHAR(
                        DATEADD(Day, -1 * 5, CURRENT_DATE),
                        'YYYYMMDD'
                   ),
                   '6'
             )::int                                                            AS order_no,

             UNIFORM(123456, 654321, RANDOM())                                  AS customer_id,

             
                'DELIVERED'                                                    AS status,
             
             DATEADD(Day, -1 * 5, CURRENT_DATE)                    AS created_at,
             current_timestamp                                                 AS changed_at

          
            UNION ALL
          
        
          SELECT
              CONCAT(
                   
    TO_VARCHAR(
                        DATEADD(Day, -1 * 5, CURRENT_DATE),
                        'YYYYMMDD'
                   ),
                   '7'
             )::int                                                            AS order_no,

             UNIFORM(123456, 654321, RANDOM())                                  AS customer_id,

             
                'DELIVERED'                                                    AS status,
             
             DATEADD(Day, -1 * 5, CURRENT_DATE)                    AS created_at,
             current_timestamp                                                 AS changed_at

          
            UNION ALL
          
        
          SELECT
              CONCAT(
                   
    TO_VARCHAR(
                        DATEADD(Day, -1 * 5, CURRENT_DATE),
                        'YYYYMMDD'
                   ),
                   '8'
             )::int                                                            AS order_no,

             UNIFORM(123456, 654321, RANDOM())                                  AS customer_id,

             
                'DELIVERED'                                                    AS status,
             
             DATEADD(Day, -1 * 5, CURRENT_DATE)                    AS created_at,
             current_timestamp                                                 AS changed_at

          
            UNION ALL
          
        
          SELECT
              CONCAT(
                   
    TO_VARCHAR(
                        DATEADD(Day, -1 * 5, CURRENT_DATE),
                        'YYYYMMDD'
                   ),
                   '9'
             )::int                                                            AS order_no,

             UNIFORM(123456, 654321, RANDOM())                                  AS customer_id,

             
                'DELIVERED'                                                    AS status,
             
             DATEADD(Day, -1 * 5, CURRENT_DATE)                    AS created_at,
             current_timestamp                                                 AS changed_at

          
        

        
            UNION ALL
        
    
        
          SELECT
              CONCAT(
                   
    TO_VARCHAR(
                        DATEADD(Day, -1 * 6, CURRENT_DATE),
                        'YYYYMMDD'
                   ),
                   '0'
             )::int                                                            AS order_no,

             UNIFORM(123456, 654321, RANDOM())                                  AS customer_id,

             
                'PENDING'                                                      AS status,
             
             DATEADD(Day, -1 * 6, CURRENT_DATE)                    AS created_at,
             current_timestamp                                                 AS changed_at

          
            UNION ALL
          
        
          SELECT
              CONCAT(
                   
    TO_VARCHAR(
                        DATEADD(Day, -1 * 6, CURRENT_DATE),
                        'YYYYMMDD'
                   ),
                   '1'
             )::int                                                            AS order_no,

             UNIFORM(123456, 654321, RANDOM())                                  AS customer_id,

             
                'DELIVERED'                                                    AS status,
             
             DATEADD(Day, -1 * 6, CURRENT_DATE)                    AS created_at,
             current_timestamp                                                 AS changed_at

          
            UNION ALL
          
        
          SELECT
              CONCAT(
                   
    TO_VARCHAR(
                        DATEADD(Day, -1 * 6, CURRENT_DATE),
                        'YYYYMMDD'
                   ),
                   '2'
             )::int                                                            AS order_no,

             UNIFORM(123456, 654321, RANDOM())                                  AS customer_id,

             
                'DELIVERED'                                                    AS status,
             
             DATEADD(Day, -1 * 6, CURRENT_DATE)                    AS created_at,
             current_timestamp                                                 AS changed_at

          
            UNION ALL
          
        
          SELECT
              CONCAT(
                   
    TO_VARCHAR(
                        DATEADD(Day, -1 * 6, CURRENT_DATE),
                        'YYYYMMDD'
                   ),
                   '3'
             )::int                                                            AS order_no,

             UNIFORM(123456, 654321, RANDOM())                                  AS customer_id,

             
                'DELIVERED'                                                    AS status,
             
             DATEADD(Day, -1 * 6, CURRENT_DATE)                    AS created_at,
             current_timestamp                                                 AS changed_at

          
            UNION ALL
          
        
          SELECT
              CONCAT(
                   
    TO_VARCHAR(
                        DATEADD(Day, -1 * 6, CURRENT_DATE),
                        'YYYYMMDD'
                   ),
                   '4'
             )::int                                                            AS order_no,

             UNIFORM(123456, 654321, RANDOM())                                  AS customer_id,

             
                'DELIVERED'                                                    AS status,
             
             DATEADD(Day, -1 * 6, CURRENT_DATE)                    AS created_at,
             current_timestamp                                                 AS changed_at

          
            UNION ALL
          
        
          SELECT
              CONCAT(
                   
    TO_VARCHAR(
                        DATEADD(Day, -1 * 6, CURRENT_DATE),
                        'YYYYMMDD'
                   ),
                   '5'
             )::int                                                            AS order_no,

             UNIFORM(123456, 654321, RANDOM())                                  AS customer_id,

             
                'DELIVERED'                                                    AS status,
             
             DATEADD(Day, -1 * 6, CURRENT_DATE)                    AS created_at,
             current_timestamp                                                 AS changed_at

          
            UNION ALL
          
        
          SELECT
              CONCAT(
                   
    TO_VARCHAR(
                        DATEADD(Day, -1 * 6, CURRENT_DATE),
                        'YYYYMMDD'
                   ),
                   '6'
             )::int                                                            AS order_no,

             UNIFORM(123456, 654321, RANDOM())                                  AS customer_id,

             
                'DELIVERED'                                                    AS status,
             
             DATEADD(Day, -1 * 6, CURRENT_DATE)                    AS created_at,
             current_timestamp                                                 AS changed_at

          
            UNION ALL
          
        
          SELECT
              CONCAT(
                   
    TO_VARCHAR(
                        DATEADD(Day, -1 * 6, CURRENT_DATE),
                        'YYYYMMDD'
                   ),
                   '7'
             )::int                                                            AS order_no,

             UNIFORM(123456, 654321, RANDOM())                                  AS customer_id,

             
                'DELIVERED'                                                    AS status,
             
             DATEADD(Day, -1 * 6, CURRENT_DATE)                    AS created_at,
             current_timestamp                                                 AS changed_at

          
            UNION ALL
          
        
          SELECT
              CONCAT(
                   
    TO_VARCHAR(
                        DATEADD(Day, -1 * 6, CURRENT_DATE),
                        'YYYYMMDD'
                   ),
                   '8'
             )::int                                                            AS order_no,

             UNIFORM(123456, 654321, RANDOM())                                  AS customer_id,

             
                'DELIVERED'                                                    AS status,
             
             DATEADD(Day, -1 * 6, CURRENT_DATE)                    AS created_at,
             current_timestamp                                                 AS changed_at

          
            UNION ALL
          
        
          SELECT
              CONCAT(
                   
    TO_VARCHAR(
                        DATEADD(Day, -1 * 6, CURRENT_DATE),
                        'YYYYMMDD'
                   ),
                   '9'
             )::int                                                            AS order_no,

             UNIFORM(123456, 654321, RANDOM())                                  AS customer_id,

             
                'DELIVERED'                                                    AS status,
             
             DATEADD(Day, -1 * 6, CURRENT_DATE)                    AS created_at,
             current_timestamp                                                 AS changed_at

          
        

        
            UNION ALL
        
    
        
          SELECT
              CONCAT(
                   
    TO_VARCHAR(
                        DATEADD(Day, -1 * 7, CURRENT_DATE),
                        'YYYYMMDD'
                   ),
                   '0'
             )::int                                                            AS order_no,

             UNIFORM(123456, 654321, RANDOM())                                  AS customer_id,

             
                'PENDING'                                                      AS status,
             
             DATEADD(Day, -1 * 7, CURRENT_DATE)                    AS created_at,
             current_timestamp                                                 AS changed_at

          
            UNION ALL
          
        
          SELECT
              CONCAT(
                   
    TO_VARCHAR(
                        DATEADD(Day, -1 * 7, CURRENT_DATE),
                        'YYYYMMDD'
                   ),
                   '1'
             )::int                                                            AS order_no,

             UNIFORM(123456, 654321, RANDOM())                                  AS customer_id,

             
                'DELIVERED'                                                    AS status,
             
             DATEADD(Day, -1 * 7, CURRENT_DATE)                    AS created_at,
             current_timestamp                                                 AS changed_at

          
            UNION ALL
          
        
          SELECT
              CONCAT(
                   
    TO_VARCHAR(
                        DATEADD(Day, -1 * 7, CURRENT_DATE),
                        'YYYYMMDD'
                   ),
                   '2'
             )::int                                                            AS order_no,

             UNIFORM(123456, 654321, RANDOM())                                  AS customer_id,

             
                'DELIVERED'                                                    AS status,
             
             DATEADD(Day, -1 * 7, CURRENT_DATE)                    AS created_at,
             current_timestamp                                                 AS changed_at

          
            UNION ALL
          
        
          SELECT
              CONCAT(
                   
    TO_VARCHAR(
                        DATEADD(Day, -1 * 7, CURRENT_DATE),
                        'YYYYMMDD'
                   ),
                   '3'
             )::int                                                            AS order_no,

             UNIFORM(123456, 654321, RANDOM())                                  AS customer_id,

             
                'DELIVERED'                                                    AS status,
             
             DATEADD(Day, -1 * 7, CURRENT_DATE)                    AS created_at,
             current_timestamp                                                 AS changed_at

          
            UNION ALL
          
        
          SELECT
              CONCAT(
                   
    TO_VARCHAR(
                        DATEADD(Day, -1 * 7, CURRENT_DATE),
                        'YYYYMMDD'
                   ),
                   '4'
             )::int                                                            AS order_no,

             UNIFORM(123456, 654321, RANDOM())                                  AS customer_id,

             
                'DELIVERED'                                                    AS status,
             
             DATEADD(Day, -1 * 7, CURRENT_DATE)                    AS created_at,
             current_timestamp                                                 AS changed_at

          
            UNION ALL
          
        
          SELECT
              CONCAT(
                   
    TO_VARCHAR(
                        DATEADD(Day, -1 * 7, CURRENT_DATE),
                        'YYYYMMDD'
                   ),
                   '5'
             )::int                                                            AS order_no,

             UNIFORM(123456, 654321, RANDOM())                                  AS customer_id,

             
                'DELIVERED'                                                    AS status,
             
             DATEADD(Day, -1 * 7, CURRENT_DATE)                    AS created_at,
             current_timestamp                                                 AS changed_at

          
            UNION ALL
          
        
          SELECT
              CONCAT(
                   
    TO_VARCHAR(
                        DATEADD(Day, -1 * 7, CURRENT_DATE),
                        'YYYYMMDD'
                   ),
                   '6'
             )::int                                                            AS order_no,

             UNIFORM(123456, 654321, RANDOM())                                  AS customer_id,

             
                'DELIVERED'                                                    AS status,
             
             DATEADD(Day, -1 * 7, CURRENT_DATE)                    AS created_at,
             current_timestamp                                                 AS changed_at

          
            UNION ALL
          
        
          SELECT
              CONCAT(
                   
    TO_VARCHAR(
                        DATEADD(Day, -1 * 7, CURRENT_DATE),
                        'YYYYMMDD'
                   ),
                   '7'
             )::int                                                            AS order_no,

             UNIFORM(123456, 654321, RANDOM())                                  AS customer_id,

             
                'DELIVERED'                                                    AS status,
             
             DATEADD(Day, -1 * 7, CURRENT_DATE)                    AS created_at,
             current_timestamp                                                 AS changed_at

          
            UNION ALL
          
        
          SELECT
              CONCAT(
                   
    TO_VARCHAR(
                        DATEADD(Day, -1 * 7, CURRENT_DATE),
                        'YYYYMMDD'
                   ),
                   '8'
             )::int                                                            AS order_no,

             UNIFORM(123456, 654321, RANDOM())                                  AS customer_id,

             
                'DELIVERED'                                                    AS status,
             
             DATEADD(Day, -1 * 7, CURRENT_DATE)                    AS created_at,
             current_timestamp                                                 AS changed_at

          
            UNION ALL
          
        
          SELECT
              CONCAT(
                   
    TO_VARCHAR(
                        DATEADD(Day, -1 * 7, CURRENT_DATE),
                        'YYYYMMDD'
                   ),
                   '9'
             )::int                                                            AS order_no,

             UNIFORM(123456, 654321, RANDOM())                                  AS customer_id,

             
                'DELIVERED'                                                    AS status,
             
             DATEADD(Day, -1 * 7, CURRENT_DATE)                    AS created_at,
             current_timestamp                                                 AS changed_at

          
        

        
            UNION ALL
        
    
        
          SELECT
              CONCAT(
                   
    TO_VARCHAR(
                        DATEADD(Day, -1 * 8, CURRENT_DATE),
                        'YYYYMMDD'
                   ),
                   '0'
             )::int                                                            AS order_no,

             UNIFORM(123456, 654321, RANDOM())                                  AS customer_id,

             
                'PENDING'                                                      AS status,
             
             DATEADD(Day, -1 * 8, CURRENT_DATE)                    AS created_at,
             current_timestamp                                                 AS changed_at

          
            UNION ALL
          
        
          SELECT
              CONCAT(
                   
    TO_VARCHAR(
                        DATEADD(Day, -1 * 8, CURRENT_DATE),
                        'YYYYMMDD'
                   ),
                   '1'
             )::int                                                            AS order_no,

             UNIFORM(123456, 654321, RANDOM())                                  AS customer_id,

             
                'DELIVERED'                                                    AS status,
             
             DATEADD(Day, -1 * 8, CURRENT_DATE)                    AS created_at,
             current_timestamp                                                 AS changed_at

          
            UNION ALL
          
        
          SELECT
              CONCAT(
                   
    TO_VARCHAR(
                        DATEADD(Day, -1 * 8, CURRENT_DATE),
                        'YYYYMMDD'
                   ),
                   '2'
             )::int                                                            AS order_no,

             UNIFORM(123456, 654321, RANDOM())                                  AS customer_id,

             
                'DELIVERED'                                                    AS status,
             
             DATEADD(Day, -1 * 8, CURRENT_DATE)                    AS created_at,
             current_timestamp                                                 AS changed_at

          
            UNION ALL
          
        
          SELECT
              CONCAT(
                   
    TO_VARCHAR(
                        DATEADD(Day, -1 * 8, CURRENT_DATE),
                        'YYYYMMDD'
                   ),
                   '3'
             )::int                                                            AS order_no,

             UNIFORM(123456, 654321, RANDOM())                                  AS customer_id,

             
                'DELIVERED'                                                    AS status,
             
             DATEADD(Day, -1 * 8, CURRENT_DATE)                    AS created_at,
             current_timestamp                                                 AS changed_at

          
            UNION ALL
          
        
          SELECT
              CONCAT(
                   
    TO_VARCHAR(
                        DATEADD(Day, -1 * 8, CURRENT_DATE),
                        'YYYYMMDD'
                   ),
                   '4'
             )::int                                                            AS order_no,

             UNIFORM(123456, 654321, RANDOM())                                  AS customer_id,

             
                'DELIVERED'                                                    AS status,
             
             DATEADD(Day, -1 * 8, CURRENT_DATE)                    AS created_at,
             current_timestamp                                                 AS changed_at

          
            UNION ALL
          
        
          SELECT
              CONCAT(
                   
    TO_VARCHAR(
                        DATEADD(Day, -1 * 8, CURRENT_DATE),
                        'YYYYMMDD'
                   ),
                   '5'
             )::int                                                            AS order_no,

             UNIFORM(123456, 654321, RANDOM())                                  AS customer_id,

             
                'DELIVERED'                                                    AS status,
             
             DATEADD(Day, -1 * 8, CURRENT_DATE)                    AS created_at,
             current_timestamp                                                 AS changed_at

          
            UNION ALL
          
        
          SELECT
              CONCAT(
                   
    TO_VARCHAR(
                        DATEADD(Day, -1 * 8, CURRENT_DATE),
                        'YYYYMMDD'
                   ),
                   '6'
             )::int                                                            AS order_no,

             UNIFORM(123456, 654321, RANDOM())                                  AS customer_id,

             
                'DELIVERED'                                                    AS status,
             
             DATEADD(Day, -1 * 8, CURRENT_DATE)                    AS created_at,
             current_timestamp                                                 AS changed_at

          
            UNION ALL
          
        
          SELECT
              CONCAT(
                   
    TO_VARCHAR(
                        DATEADD(Day, -1 * 8, CURRENT_DATE),
                        'YYYYMMDD'
                   ),
                   '7'
             )::int                                                            AS order_no,

             UNIFORM(123456, 654321, RANDOM())                                  AS customer_id,

             
                'DELIVERED'                                                    AS status,
             
             DATEADD(Day, -1 * 8, CURRENT_DATE)                    AS created_at,
             current_timestamp                                                 AS changed_at

          
            UNION ALL
          
        
          SELECT
              CONCAT(
                   
    TO_VARCHAR(
                        DATEADD(Day, -1 * 8, CURRENT_DATE),
                        'YYYYMMDD'
                   ),
                   '8'
             )::int                                                            AS order_no,

             UNIFORM(123456, 654321, RANDOM())                                  AS customer_id,

             
                'DELIVERED'                                                    AS status,
             
             DATEADD(Day, -1 * 8, CURRENT_DATE)                    AS created_at,
             current_timestamp                                                 AS changed_at

          
            UNION ALL
          
        
          SELECT
              CONCAT(
                   
    TO_VARCHAR(
                        DATEADD(Day, -1 * 8, CURRENT_DATE),
                        'YYYYMMDD'
                   ),
                   '9'
             )::int                                                            AS order_no,

             UNIFORM(123456, 654321, RANDOM())                                  AS customer_id,

             
                'DELIVERED'                                                    AS status,
             
             DATEADD(Day, -1 * 8, CURRENT_DATE)                    AS created_at,
             current_timestamp                                                 AS changed_at

          
        

        
            UNION ALL
        
    
        
          SELECT
              CONCAT(
                   
    TO_VARCHAR(
                        DATEADD(Day, -1 * 9, CURRENT_DATE),
                        'YYYYMMDD'
                   ),
                   '0'
             )::int                                                            AS order_no,

             UNIFORM(123456, 654321, RANDOM())                                  AS customer_id,

             
                'PENDING'                                                      AS status,
             
             DATEADD(Day, -1 * 9, CURRENT_DATE)                    AS created_at,
             current_timestamp                                                 AS changed_at

          
            UNION ALL
          
        
          SELECT
              CONCAT(
                   
    TO_VARCHAR(
                        DATEADD(Day, -1 * 9, CURRENT_DATE),
                        'YYYYMMDD'
                   ),
                   '1'
             )::int                                                            AS order_no,

             UNIFORM(123456, 654321, RANDOM())                                  AS customer_id,

             
                'DELIVERED'                                                    AS status,
             
             DATEADD(Day, -1 * 9, CURRENT_DATE)                    AS created_at,
             current_timestamp                                                 AS changed_at

          
            UNION ALL
          
        
          SELECT
              CONCAT(
                   
    TO_VARCHAR(
                        DATEADD(Day, -1 * 9, CURRENT_DATE),
                        'YYYYMMDD'
                   ),
                   '2'
             )::int                                                            AS order_no,

             UNIFORM(123456, 654321, RANDOM())                                  AS customer_id,

             
                'DELIVERED'                                                    AS status,
             
             DATEADD(Day, -1 * 9, CURRENT_DATE)                    AS created_at,
             current_timestamp                                                 AS changed_at

          
            UNION ALL
          
        
          SELECT
              CONCAT(
                   
    TO_VARCHAR(
                        DATEADD(Day, -1 * 9, CURRENT_DATE),
                        'YYYYMMDD'
                   ),
                   '3'
             )::int                                                            AS order_no,

             UNIFORM(123456, 654321, RANDOM())                                  AS customer_id,

             
                'DELIVERED'                                                    AS status,
             
             DATEADD(Day, -1 * 9, CURRENT_DATE)                    AS created_at,
             current_timestamp                                                 AS changed_at

          
            UNION ALL
          
        
          SELECT
              CONCAT(
                   
    TO_VARCHAR(
                        DATEADD(Day, -1 * 9, CURRENT_DATE),
                        'YYYYMMDD'
                   ),
                   '4'
             )::int                                                            AS order_no,

             UNIFORM(123456, 654321, RANDOM())                                  AS customer_id,

             
                'DELIVERED'                                                    AS status,
             
             DATEADD(Day, -1 * 9, CURRENT_DATE)                    AS created_at,
             current_timestamp                                                 AS changed_at

          
            UNION ALL
          
        
          SELECT
              CONCAT(
                   
    TO_VARCHAR(
                        DATEADD(Day, -1 * 9, CURRENT_DATE),
                        'YYYYMMDD'
                   ),
                   '5'
             )::int                                                            AS order_no,

             UNIFORM(123456, 654321, RANDOM())                                  AS customer_id,

             
                'DELIVERED'                                                    AS status,
             
             DATEADD(Day, -1 * 9, CURRENT_DATE)                    AS created_at,
             current_timestamp                                                 AS changed_at

          
            UNION ALL
          
        
          SELECT
              CONCAT(
                   
    TO_VARCHAR(
                        DATEADD(Day, -1 * 9, CURRENT_DATE),
                        'YYYYMMDD'
                   ),
                   '6'
             )::int                                                            AS order_no,

             UNIFORM(123456, 654321, RANDOM())                                  AS customer_id,

             
                'DELIVERED'                                                    AS status,
             
             DATEADD(Day, -1 * 9, CURRENT_DATE)                    AS created_at,
             current_timestamp                                                 AS changed_at

          
            UNION ALL
          
        
          SELECT
              CONCAT(
                   
    TO_VARCHAR(
                        DATEADD(Day, -1 * 9, CURRENT_DATE),
                        'YYYYMMDD'
                   ),
                   '7'
             )::int                                                            AS order_no,

             UNIFORM(123456, 654321, RANDOM())                                  AS customer_id,

             
                'DELIVERED'                                                    AS status,
             
             DATEADD(Day, -1 * 9, CURRENT_DATE)                    AS created_at,
             current_timestamp                                                 AS changed_at

          
            UNION ALL
          
        
          SELECT
              CONCAT(
                   
    TO_VARCHAR(
                        DATEADD(Day, -1 * 9, CURRENT_DATE),
                        'YYYYMMDD'
                   ),
                   '8'
             )::int                                                            AS order_no,

             UNIFORM(123456, 654321, RANDOM())                                  AS customer_id,

             
                'DELIVERED'                                                    AS status,
             
             DATEADD(Day, -1 * 9, CURRENT_DATE)                    AS created_at,
             current_timestamp                                                 AS changed_at

          
            UNION ALL
          
        
          SELECT
              CONCAT(
                   
    TO_VARCHAR(
                        DATEADD(Day, -1 * 9, CURRENT_DATE),
                        'YYYYMMDD'
                   ),
                   '9'
             )::int                                                            AS order_no,

             UNIFORM(123456, 654321, RANDOM())                                  AS customer_id,

             
                'DELIVERED'                                                    AS status,
             
             DATEADD(Day, -1 * 9, CURRENT_DATE)                    AS created_at,
             current_timestamp                                                 AS changed_at

          
        

        
            UNION ALL
        
    
        
          SELECT
              CONCAT(
                   
    TO_VARCHAR(
                        DATEADD(Day, -1 * 10, CURRENT_DATE),
                        'YYYYMMDD'
                   ),
                   '0'
             )::int                                                            AS order_no,

             UNIFORM(123456, 654321, RANDOM())                                  AS customer_id,

             
                'PENDING'                                                      AS status,
             
             DATEADD(Day, -1 * 10, CURRENT_DATE)                    AS created_at,
             current_timestamp                                                 AS changed_at

          
            UNION ALL
          
        
          SELECT
              CONCAT(
                   
    TO_VARCHAR(
                        DATEADD(Day, -1 * 10, CURRENT_DATE),
                        'YYYYMMDD'
                   ),
                   '1'
             )::int                                                            AS order_no,

             UNIFORM(123456, 654321, RANDOM())                                  AS customer_id,

             
                'DELIVERED'                                                    AS status,
             
             DATEADD(Day, -1 * 10, CURRENT_DATE)                    AS created_at,
             current_timestamp                                                 AS changed_at

          
            UNION ALL
          
        
          SELECT
              CONCAT(
                   
    TO_VARCHAR(
                        DATEADD(Day, -1 * 10, CURRENT_DATE),
                        'YYYYMMDD'
                   ),
                   '2'
             )::int                                                            AS order_no,

             UNIFORM(123456, 654321, RANDOM())                                  AS customer_id,

             
                'DELIVERED'                                                    AS status,
             
             DATEADD(Day, -1 * 10, CURRENT_DATE)                    AS created_at,
             current_timestamp                                                 AS changed_at

          
            UNION ALL
          
        
          SELECT
              CONCAT(
                   
    TO_VARCHAR(
                        DATEADD(Day, -1 * 10, CURRENT_DATE),
                        'YYYYMMDD'
                   ),
                   '3'
             )::int                                                            AS order_no,

             UNIFORM(123456, 654321, RANDOM())                                  AS customer_id,

             
                'DELIVERED'                                                    AS status,
             
             DATEADD(Day, -1 * 10, CURRENT_DATE)                    AS created_at,
             current_timestamp                                                 AS changed_at

          
            UNION ALL
          
        
          SELECT
              CONCAT(
                   
    TO_VARCHAR(
                        DATEADD(Day, -1 * 10, CURRENT_DATE),
                        'YYYYMMDD'
                   ),
                   '4'
             )::int                                                            AS order_no,

             UNIFORM(123456, 654321, RANDOM())                                  AS customer_id,

             
                'DELIVERED'                                                    AS status,
             
             DATEADD(Day, -1 * 10, CURRENT_DATE)                    AS created_at,
             current_timestamp                                                 AS changed_at

          
            UNION ALL
          
        
          SELECT
              CONCAT(
                   
    TO_VARCHAR(
                        DATEADD(Day, -1 * 10, CURRENT_DATE),
                        'YYYYMMDD'
                   ),
                   '5'
             )::int                                                            AS order_no,

             UNIFORM(123456, 654321, RANDOM())                                  AS customer_id,

             
                'DELIVERED'                                                    AS status,
             
             DATEADD(Day, -1 * 10, CURRENT_DATE)                    AS created_at,
             current_timestamp                                                 AS changed_at

          
            UNION ALL
          
        
          SELECT
              CONCAT(
                   
    TO_VARCHAR(
                        DATEADD(Day, -1 * 10, CURRENT_DATE),
                        'YYYYMMDD'
                   ),
                   '6'
             )::int                                                            AS order_no,

             UNIFORM(123456, 654321, RANDOM())                                  AS customer_id,

             
                'DELIVERED'                                                    AS status,
             
             DATEADD(Day, -1 * 10, CURRENT_DATE)                    AS created_at,
             current_timestamp                                                 AS changed_at

          
            UNION ALL
          
        
          SELECT
              CONCAT(
                   
    TO_VARCHAR(
                        DATEADD(Day, -1 * 10, CURRENT_DATE),
                        'YYYYMMDD'
                   ),
                   '7'
             )::int                                                            AS order_no,

             UNIFORM(123456, 654321, RANDOM())                                  AS customer_id,

             
                'DELIVERED'                                                    AS status,
             
             DATEADD(Day, -1 * 10, CURRENT_DATE)                    AS created_at,
             current_timestamp                                                 AS changed_at

          
            UNION ALL
          
        
          SELECT
              CONCAT(
                   
    TO_VARCHAR(
                        DATEADD(Day, -1 * 10, CURRENT_DATE),
                        'YYYYMMDD'
                   ),
                   '8'
             )::int                                                            AS order_no,

             UNIFORM(123456, 654321, RANDOM())                                  AS customer_id,

             
                'DELIVERED'                                                    AS status,
             
             DATEADD(Day, -1 * 10, CURRENT_DATE)                    AS created_at,
             current_timestamp                                                 AS changed_at

          
            UNION ALL
          
        
          SELECT
              CONCAT(
                   
    TO_VARCHAR(
                        DATEADD(Day, -1 * 10, CURRENT_DATE),
                        'YYYYMMDD'
                   ),
                   '9'
             )::int                                                            AS order_no,

             UNIFORM(123456, 654321, RANDOM())                                  AS customer_id,

             
                'DELIVERED'                                                    AS status,
             
             DATEADD(Day, -1 * 10, CURRENT_DATE)                    AS created_at,
             current_timestamp                                                 AS changed_at

          
        

        
            UNION ALL
        
    
        
          SELECT
              CONCAT(
                   
    TO_VARCHAR(
                        DATEADD(Day, -1 * 11, CURRENT_DATE),
                        'YYYYMMDD'
                   ),
                   '0'
             )::int                                                            AS order_no,

             UNIFORM(123456, 654321, RANDOM())                                  AS customer_id,

             
                'PENDING'                                                      AS status,
             
             DATEADD(Day, -1 * 11, CURRENT_DATE)                    AS created_at,
             current_timestamp                                                 AS changed_at

          
            UNION ALL
          
        
          SELECT
              CONCAT(
                   
    TO_VARCHAR(
                        DATEADD(Day, -1 * 11, CURRENT_DATE),
                        'YYYYMMDD'
                   ),
                   '1'
             )::int                                                            AS order_no,

             UNIFORM(123456, 654321, RANDOM())                                  AS customer_id,

             
                'DELIVERED'                                                    AS status,
             
             DATEADD(Day, -1 * 11, CURRENT_DATE)                    AS created_at,
             current_timestamp                                                 AS changed_at

          
            UNION ALL
          
        
          SELECT
              CONCAT(
                   
    TO_VARCHAR(
                        DATEADD(Day, -1 * 11, CURRENT_DATE),
                        'YYYYMMDD'
                   ),
                   '2'
             )::int                                                            AS order_no,

             UNIFORM(123456, 654321, RANDOM())                                  AS customer_id,

             
                'DELIVERED'                                                    AS status,
             
             DATEADD(Day, -1 * 11, CURRENT_DATE)                    AS created_at,
             current_timestamp                                                 AS changed_at

          
            UNION ALL
          
        
          SELECT
              CONCAT(
                   
    TO_VARCHAR(
                        DATEADD(Day, -1 * 11, CURRENT_DATE),
                        'YYYYMMDD'
                   ),
                   '3'
             )::int                                                            AS order_no,

             UNIFORM(123456, 654321, RANDOM())                                  AS customer_id,

             
                'DELIVERED'                                                    AS status,
             
             DATEADD(Day, -1 * 11, CURRENT_DATE)                    AS created_at,
             current_timestamp                                                 AS changed_at

          
            UNION ALL
          
        
          SELECT
              CONCAT(
                   
    TO_VARCHAR(
                        DATEADD(Day, -1 * 11, CURRENT_DATE),
                        'YYYYMMDD'
                   ),
                   '4'
             )::int                                                            AS order_no,

             UNIFORM(123456, 654321, RANDOM())                                  AS customer_id,

             
                'DELIVERED'                                                    AS status,
             
             DATEADD(Day, -1 * 11, CURRENT_DATE)                    AS created_at,
             current_timestamp                                                 AS changed_at

          
            UNION ALL
          
        
          SELECT
              CONCAT(
                   
    TO_VARCHAR(
                        DATEADD(Day, -1 * 11, CURRENT_DATE),
                        'YYYYMMDD'
                   ),
                   '5'
             )::int                                                            AS order_no,

             UNIFORM(123456, 654321, RANDOM())                                  AS customer_id,

             
                'DELIVERED'                                                    AS status,
             
             DATEADD(Day, -1 * 11, CURRENT_DATE)                    AS created_at,
             current_timestamp                                                 AS changed_at

          
            UNION ALL
          
        
          SELECT
              CONCAT(
                   
    TO_VARCHAR(
                        DATEADD(Day, -1 * 11, CURRENT_DATE),
                        'YYYYMMDD'
                   ),
                   '6'
             )::int                                                            AS order_no,

             UNIFORM(123456, 654321, RANDOM())                                  AS customer_id,

             
                'DELIVERED'                                                    AS status,
             
             DATEADD(Day, -1 * 11, CURRENT_DATE)                    AS created_at,
             current_timestamp                                                 AS changed_at

          
            UNION ALL
          
        
          SELECT
              CONCAT(
                   
    TO_VARCHAR(
                        DATEADD(Day, -1 * 11, CURRENT_DATE),
                        'YYYYMMDD'
                   ),
                   '7'
             )::int                                                            AS order_no,

             UNIFORM(123456, 654321, RANDOM())                                  AS customer_id,

             
                'DELIVERED'                                                    AS status,
             
             DATEADD(Day, -1 * 11, CURRENT_DATE)                    AS created_at,
             current_timestamp                                                 AS changed_at

          
            UNION ALL
          
        
          SELECT
              CONCAT(
                   
    TO_VARCHAR(
                        DATEADD(Day, -1 * 11, CURRENT_DATE),
                        'YYYYMMDD'
                   ),
                   '8'
             )::int                                                            AS order_no,

             UNIFORM(123456, 654321, RANDOM())                                  AS customer_id,

             
                'DELIVERED'                                                    AS status,
             
             DATEADD(Day, -1 * 11, CURRENT_DATE)                    AS created_at,
             current_timestamp                                                 AS changed_at

          
            UNION ALL
          
        
          SELECT
              CONCAT(
                   
    TO_VARCHAR(
                        DATEADD(Day, -1 * 11, CURRENT_DATE),
                        'YYYYMMDD'
                   ),
                   '9'
             )::int                                                            AS order_no,

             UNIFORM(123456, 654321, RANDOM())                                  AS customer_id,

             
                'DELIVERED'                                                    AS status,
             
             DATEADD(Day, -1 * 11, CURRENT_DATE)                    AS created_at,
             current_timestamp                                                 AS changed_at

          
        

        
            UNION ALL
        
    
        
          SELECT
              CONCAT(
                   
    TO_VARCHAR(
                        DATEADD(Day, -1 * 12, CURRENT_DATE),
                        'YYYYMMDD'
                   ),
                   '0'
             )::int                                                            AS order_no,

             UNIFORM(123456, 654321, RANDOM())                                  AS customer_id,

             
                'PENDING'                                                      AS status,
             
             DATEADD(Day, -1 * 12, CURRENT_DATE)                    AS created_at,
             current_timestamp                                                 AS changed_at

          
            UNION ALL
          
        
          SELECT
              CONCAT(
                   
    TO_VARCHAR(
                        DATEADD(Day, -1 * 12, CURRENT_DATE),
                        'YYYYMMDD'
                   ),
                   '1'
             )::int                                                            AS order_no,

             UNIFORM(123456, 654321, RANDOM())                                  AS customer_id,

             
                'DELIVERED'                                                    AS status,
             
             DATEADD(Day, -1 * 12, CURRENT_DATE)                    AS created_at,
             current_timestamp                                                 AS changed_at

          
            UNION ALL
          
        
          SELECT
              CONCAT(
                   
    TO_VARCHAR(
                        DATEADD(Day, -1 * 12, CURRENT_DATE),
                        'YYYYMMDD'
                   ),
                   '2'
             )::int                                                            AS order_no,

             UNIFORM(123456, 654321, RANDOM())                                  AS customer_id,

             
                'DELIVERED'                                                    AS status,
             
             DATEADD(Day, -1 * 12, CURRENT_DATE)                    AS created_at,
             current_timestamp                                                 AS changed_at

          
            UNION ALL
          
        
          SELECT
              CONCAT(
                   
    TO_VARCHAR(
                        DATEADD(Day, -1 * 12, CURRENT_DATE),
                        'YYYYMMDD'
                   ),
                   '3'
             )::int                                                            AS order_no,

             UNIFORM(123456, 654321, RANDOM())                                  AS customer_id,

             
                'DELIVERED'                                                    AS status,
             
             DATEADD(Day, -1 * 12, CURRENT_DATE)                    AS created_at,
             current_timestamp                                                 AS changed_at

          
            UNION ALL
          
        
          SELECT
              CONCAT(
                   
    TO_VARCHAR(
                        DATEADD(Day, -1 * 12, CURRENT_DATE),
                        'YYYYMMDD'
                   ),
                   '4'
             )::int                                                            AS order_no,

             UNIFORM(123456, 654321, RANDOM())                                  AS customer_id,

             
                'DELIVERED'                                                    AS status,
             
             DATEADD(Day, -1 * 12, CURRENT_DATE)                    AS created_at,
             current_timestamp                                                 AS changed_at

          
            UNION ALL
          
        
          SELECT
              CONCAT(
                   
    TO_VARCHAR(
                        DATEADD(Day, -1 * 12, CURRENT_DATE),
                        'YYYYMMDD'
                   ),
                   '5'
             )::int                                                            AS order_no,

             UNIFORM(123456, 654321, RANDOM())                                  AS customer_id,

             
                'DELIVERED'                                                    AS status,
             
             DATEADD(Day, -1 * 12, CURRENT_DATE)                    AS created_at,
             current_timestamp                                                 AS changed_at

          
            UNION ALL
          
        
          SELECT
              CONCAT(
                   
    TO_VARCHAR(
                        DATEADD(Day, -1 * 12, CURRENT_DATE),
                        'YYYYMMDD'
                   ),
                   '6'
             )::int                                                            AS order_no,

             UNIFORM(123456, 654321, RANDOM())                                  AS customer_id,

             
                'DELIVERED'                                                    AS status,
             
             DATEADD(Day, -1 * 12, CURRENT_DATE)                    AS created_at,
             current_timestamp                                                 AS changed_at

          
            UNION ALL
          
        
          SELECT
              CONCAT(
                   
    TO_VARCHAR(
                        DATEADD(Day, -1 * 12, CURRENT_DATE),
                        'YYYYMMDD'
                   ),
                   '7'
             )::int                                                            AS order_no,

             UNIFORM(123456, 654321, RANDOM())                                  AS customer_id,

             
                'DELIVERED'                                                    AS status,
             
             DATEADD(Day, -1 * 12, CURRENT_DATE)                    AS created_at,
             current_timestamp                                                 AS changed_at

          
            UNION ALL
          
        
          SELECT
              CONCAT(
                   
    TO_VARCHAR(
                        DATEADD(Day, -1 * 12, CURRENT_DATE),
                        'YYYYMMDD'
                   ),
                   '8'
             )::int                                                            AS order_no,

             UNIFORM(123456, 654321, RANDOM())                                  AS customer_id,

             
                'DELIVERED'                                                    AS status,
             
             DATEADD(Day, -1 * 12, CURRENT_DATE)                    AS created_at,
             current_timestamp                                                 AS changed_at

          
            UNION ALL
          
        
          SELECT
              CONCAT(
                   
    TO_VARCHAR(
                        DATEADD(Day, -1 * 12, CURRENT_DATE),
                        'YYYYMMDD'
                   ),
                   '9'
             )::int                                                            AS order_no,

             UNIFORM(123456, 654321, RANDOM())                                  AS customer_id,

             
                'DELIVERED'                                                    AS status,
             
             DATEADD(Day, -1 * 12, CURRENT_DATE)                    AS created_at,
             current_timestamp                                                 AS changed_at

          
        

        
            UNION ALL
        
    
        
          SELECT
              CONCAT(
                   
    TO_VARCHAR(
                        DATEADD(Day, -1 * 13, CURRENT_DATE),
                        'YYYYMMDD'
                   ),
                   '0'
             )::int                                                            AS order_no,

             UNIFORM(123456, 654321, RANDOM())                                  AS customer_id,

             
                'PENDING'                                                      AS status,
             
             DATEADD(Day, -1 * 13, CURRENT_DATE)                    AS created_at,
             current_timestamp                                                 AS changed_at

          
            UNION ALL
          
        
          SELECT
              CONCAT(
                   
    TO_VARCHAR(
                        DATEADD(Day, -1 * 13, CURRENT_DATE),
                        'YYYYMMDD'
                   ),
                   '1'
             )::int                                                            AS order_no,

             UNIFORM(123456, 654321, RANDOM())                                  AS customer_id,

             
                'DELIVERED'                                                    AS status,
             
             DATEADD(Day, -1 * 13, CURRENT_DATE)                    AS created_at,
             current_timestamp                                                 AS changed_at

          
            UNION ALL
          
        
          SELECT
              CONCAT(
                   
    TO_VARCHAR(
                        DATEADD(Day, -1 * 13, CURRENT_DATE),
                        'YYYYMMDD'
                   ),
                   '2'
             )::int                                                            AS order_no,

             UNIFORM(123456, 654321, RANDOM())                                  AS customer_id,

             
                'DELIVERED'                                                    AS status,
             
             DATEADD(Day, -1 * 13, CURRENT_DATE)                    AS created_at,
             current_timestamp                                                 AS changed_at

          
            UNION ALL
          
        
          SELECT
              CONCAT(
                   
    TO_VARCHAR(
                        DATEADD(Day, -1 * 13, CURRENT_DATE),
                        'YYYYMMDD'
                   ),
                   '3'
             )::int                                                            AS order_no,

             UNIFORM(123456, 654321, RANDOM())                                  AS customer_id,

             
                'DELIVERED'                                                    AS status,
             
             DATEADD(Day, -1 * 13, CURRENT_DATE)                    AS created_at,
             current_timestamp                                                 AS changed_at

          
            UNION ALL
          
        
          SELECT
              CONCAT(
                   
    TO_VARCHAR(
                        DATEADD(Day, -1 * 13, CURRENT_DATE),
                        'YYYYMMDD'
                   ),
                   '4'
             )::int                                                            AS order_no,

             UNIFORM(123456, 654321, RANDOM())                                  AS customer_id,

             
                'DELIVERED'                                                    AS status,
             
             DATEADD(Day, -1 * 13, CURRENT_DATE)                    AS created_at,
             current_timestamp                                                 AS changed_at

          
            UNION ALL
          
        
          SELECT
              CONCAT(
                   
    TO_VARCHAR(
                        DATEADD(Day, -1 * 13, CURRENT_DATE),
                        'YYYYMMDD'
                   ),
                   '5'
             )::int                                                            AS order_no,

             UNIFORM(123456, 654321, RANDOM())                                  AS customer_id,

             
                'DELIVERED'                                                    AS status,
             
             DATEADD(Day, -1 * 13, CURRENT_DATE)                    AS created_at,
             current_timestamp                                                 AS changed_at

          
            UNION ALL
          
        
          SELECT
              CONCAT(
                   
    TO_VARCHAR(
                        DATEADD(Day, -1 * 13, CURRENT_DATE),
                        'YYYYMMDD'
                   ),
                   '6'
             )::int                                                            AS order_no,

             UNIFORM(123456, 654321, RANDOM())                                  AS customer_id,

             
                'DELIVERED'                                                    AS status,
             
             DATEADD(Day, -1 * 13, CURRENT_DATE)                    AS created_at,
             current_timestamp                                                 AS changed_at

          
            UNION ALL
          
        
          SELECT
              CONCAT(
                   
    TO_VARCHAR(
                        DATEADD(Day, -1 * 13, CURRENT_DATE),
                        'YYYYMMDD'
                   ),
                   '7'
             )::int                                                            AS order_no,

             UNIFORM(123456, 654321, RANDOM())                                  AS customer_id,

             
                'DELIVERED'                                                    AS status,
             
             DATEADD(Day, -1 * 13, CURRENT_DATE)                    AS created_at,
             current_timestamp                                                 AS changed_at

          
            UNION ALL
          
        
          SELECT
              CONCAT(
                   
    TO_VARCHAR(
                        DATEADD(Day, -1 * 13, CURRENT_DATE),
                        'YYYYMMDD'
                   ),
                   '8'
             )::int                                                            AS order_no,

             UNIFORM(123456, 654321, RANDOM())                                  AS customer_id,

             
                'DELIVERED'                                                    AS status,
             
             DATEADD(Day, -1 * 13, CURRENT_DATE)                    AS created_at,
             current_timestamp                                                 AS changed_at

          
            UNION ALL
          
        
          SELECT
              CONCAT(
                   
    TO_VARCHAR(
                        DATEADD(Day, -1 * 13, CURRENT_DATE),
                        'YYYYMMDD'
                   ),
                   '9'
             )::int                                                            AS order_no,

             UNIFORM(123456, 654321, RANDOM())                                  AS customer_id,

             
                'DELIVERED'                                                    AS status,
             
             DATEADD(Day, -1 * 13, CURRENT_DATE)                    AS created_at,
             current_timestamp                                                 AS changed_at

          
        

        
            UNION ALL
        
    
        
          SELECT
              CONCAT(
                   
    TO_VARCHAR(
                        DATEADD(Day, -1 * 14, CURRENT_DATE),
                        'YYYYMMDD'
                   ),
                   '0'
             )::int                                                            AS order_no,

             UNIFORM(123456, 654321, RANDOM())                                  AS customer_id,

             
                'PENDING'                                                      AS status,
             
             DATEADD(Day, -1 * 14, CURRENT_DATE)                    AS created_at,
             current_timestamp                                                 AS changed_at

          
            UNION ALL
          
        
          SELECT
              CONCAT(
                   
    TO_VARCHAR(
                        DATEADD(Day, -1 * 14, CURRENT_DATE),
                        'YYYYMMDD'
                   ),
                   '1'
             )::int                                                            AS order_no,

             UNIFORM(123456, 654321, RANDOM())                                  AS customer_id,

             
                'DELIVERED'                                                    AS status,
             
             DATEADD(Day, -1 * 14, CURRENT_DATE)                    AS created_at,
             current_timestamp                                                 AS changed_at

          
            UNION ALL
          
        
          SELECT
              CONCAT(
                   
    TO_VARCHAR(
                        DATEADD(Day, -1 * 14, CURRENT_DATE),
                        'YYYYMMDD'
                   ),
                   '2'
             )::int                                                            AS order_no,

             UNIFORM(123456, 654321, RANDOM())                                  AS customer_id,

             
                'DELIVERED'                                                    AS status,
             
             DATEADD(Day, -1 * 14, CURRENT_DATE)                    AS created_at,
             current_timestamp                                                 AS changed_at

          
            UNION ALL
          
        
          SELECT
              CONCAT(
                   
    TO_VARCHAR(
                        DATEADD(Day, -1 * 14, CURRENT_DATE),
                        'YYYYMMDD'
                   ),
                   '3'
             )::int                                                            AS order_no,

             UNIFORM(123456, 654321, RANDOM())                                  AS customer_id,

             
                'DELIVERED'                                                    AS status,
             
             DATEADD(Day, -1 * 14, CURRENT_DATE)                    AS created_at,
             current_timestamp                                                 AS changed_at

          
            UNION ALL
          
        
          SELECT
              CONCAT(
                   
    TO_VARCHAR(
                        DATEADD(Day, -1 * 14, CURRENT_DATE),
                        'YYYYMMDD'
                   ),
                   '4'
             )::int                                                            AS order_no,

             UNIFORM(123456, 654321, RANDOM())                                  AS customer_id,

             
                'DELIVERED'                                                    AS status,
             
             DATEADD(Day, -1 * 14, CURRENT_DATE)                    AS created_at,
             current_timestamp                                                 AS changed_at

          
            UNION ALL
          
        
          SELECT
              CONCAT(
                   
    TO_VARCHAR(
                        DATEADD(Day, -1 * 14, CURRENT_DATE),
                        'YYYYMMDD'
                   ),
                   '5'
             )::int                                                            AS order_no,

             UNIFORM(123456, 654321, RANDOM())                                  AS customer_id,

             
                'DELIVERED'                                                    AS status,
             
             DATEADD(Day, -1 * 14, CURRENT_DATE)                    AS created_at,
             current_timestamp                                                 AS changed_at

          
            UNION ALL
          
        
          SELECT
              CONCAT(
                   
    TO_VARCHAR(
                        DATEADD(Day, -1 * 14, CURRENT_DATE),
                        'YYYYMMDD'
                   ),
                   '6'
             )::int                                                            AS order_no,

             UNIFORM(123456, 654321, RANDOM())                                  AS customer_id,

             
                'DELIVERED'                                                    AS status,
             
             DATEADD(Day, -1 * 14, CURRENT_DATE)                    AS created_at,
             current_timestamp                                                 AS changed_at

          
            UNION ALL
          
        
          SELECT
              CONCAT(
                   
    TO_VARCHAR(
                        DATEADD(Day, -1 * 14, CURRENT_DATE),
                        'YYYYMMDD'
                   ),
                   '7'
             )::int                                                            AS order_no,

             UNIFORM(123456, 654321, RANDOM())                                  AS customer_id,

             
                'DELIVERED'                                                    AS status,
             
             DATEADD(Day, -1 * 14, CURRENT_DATE)                    AS created_at,
             current_timestamp                                                 AS changed_at

          
            UNION ALL
          
        
          SELECT
              CONCAT(
                   
    TO_VARCHAR(
                        DATEADD(Day, -1 * 14, CURRENT_DATE),
                        'YYYYMMDD'
                   ),
                   '8'
             )::int                                                            AS order_no,

             UNIFORM(123456, 654321, RANDOM())                                  AS customer_id,

             
                'DELIVERED'                                                    AS status,
             
             DATEADD(Day, -1 * 14, CURRENT_DATE)                    AS created_at,
             current_timestamp                                                 AS changed_at

          
            UNION ALL
          
        
          SELECT
              CONCAT(
                   
    TO_VARCHAR(
                        DATEADD(Day, -1 * 14, CURRENT_DATE),
                        'YYYYMMDD'
                   ),
                   '9'
             )::int                                                            AS order_no,

             UNIFORM(123456, 654321, RANDOM())                                  AS customer_id,

             
                'DELIVERED'                                                    AS status,
             
             DATEADD(Day, -1 * 14, CURRENT_DATE)                    AS created_at,
             current_timestamp                                                 AS changed_at

          
        

        
            UNION ALL
        
    
        
          SELECT
              CONCAT(
                   
    TO_VARCHAR(
                        DATEADD(Day, -1 * 15, CURRENT_DATE),
                        'YYYYMMDD'
                   ),
                   '0'
             )::int                                                            AS order_no,

             UNIFORM(123456, 654321, RANDOM())                                  AS customer_id,

             
                'PENDING'                                                      AS status,
             
             DATEADD(Day, -1 * 15, CURRENT_DATE)                    AS created_at,
             current_timestamp                                                 AS changed_at

          
            UNION ALL
          
        
          SELECT
              CONCAT(
                   
    TO_VARCHAR(
                        DATEADD(Day, -1 * 15, CURRENT_DATE),
                        'YYYYMMDD'
                   ),
                   '1'
             )::int                                                            AS order_no,

             UNIFORM(123456, 654321, RANDOM())                                  AS customer_id,

             
                'DELIVERED'                                                    AS status,
             
             DATEADD(Day, -1 * 15, CURRENT_DATE)                    AS created_at,
             current_timestamp                                                 AS changed_at

          
            UNION ALL
          
        
          SELECT
              CONCAT(
                   
    TO_VARCHAR(
                        DATEADD(Day, -1 * 15, CURRENT_DATE),
                        'YYYYMMDD'
                   ),
                   '2'
             )::int                                                            AS order_no,

             UNIFORM(123456, 654321, RANDOM())                                  AS customer_id,

             
                'DELIVERED'                                                    AS status,
             
             DATEADD(Day, -1 * 15, CURRENT_DATE)                    AS created_at,
             current_timestamp                                                 AS changed_at

          
            UNION ALL
          
        
          SELECT
              CONCAT(
                   
    TO_VARCHAR(
                        DATEADD(Day, -1 * 15, CURRENT_DATE),
                        'YYYYMMDD'
                   ),
                   '3'
             )::int                                                            AS order_no,

             UNIFORM(123456, 654321, RANDOM())                                  AS customer_id,

             
                'DELIVERED'                                                    AS status,
             
             DATEADD(Day, -1 * 15, CURRENT_DATE)                    AS created_at,
             current_timestamp                                                 AS changed_at

          
            UNION ALL
          
        
          SELECT
              CONCAT(
                   
    TO_VARCHAR(
                        DATEADD(Day, -1 * 15, CURRENT_DATE),
                        'YYYYMMDD'
                   ),
                   '4'
             )::int                                                            AS order_no,

             UNIFORM(123456, 654321, RANDOM())                                  AS customer_id,

             
                'DELIVERED'                                                    AS status,
             
             DATEADD(Day, -1 * 15, CURRENT_DATE)                    AS created_at,
             current_timestamp                                                 AS changed_at

          
            UNION ALL
          
        
          SELECT
              CONCAT(
                   
    TO_VARCHAR(
                        DATEADD(Day, -1 * 15, CURRENT_DATE),
                        'YYYYMMDD'
                   ),
                   '5'
             )::int                                                            AS order_no,

             UNIFORM(123456, 654321, RANDOM())                                  AS customer_id,

             
                'DELIVERED'                                                    AS status,
             
             DATEADD(Day, -1 * 15, CURRENT_DATE)                    AS created_at,
             current_timestamp                                                 AS changed_at

          
            UNION ALL
          
        
          SELECT
              CONCAT(
                   
    TO_VARCHAR(
                        DATEADD(Day, -1 * 15, CURRENT_DATE),
                        'YYYYMMDD'
                   ),
                   '6'
             )::int                                                            AS order_no,

             UNIFORM(123456, 654321, RANDOM())                                  AS customer_id,

             
                'DELIVERED'                                                    AS status,
             
             DATEADD(Day, -1 * 15, CURRENT_DATE)                    AS created_at,
             current_timestamp                                                 AS changed_at

          
            UNION ALL
          
        
          SELECT
              CONCAT(
                   
    TO_VARCHAR(
                        DATEADD(Day, -1 * 15, CURRENT_DATE),
                        'YYYYMMDD'
                   ),
                   '7'
             )::int                                                            AS order_no,

             UNIFORM(123456, 654321, RANDOM())                                  AS customer_id,

             
                'DELIVERED'                                                    AS status,
             
             DATEADD(Day, -1 * 15, CURRENT_DATE)                    AS created_at,
             current_timestamp                                                 AS changed_at

          
            UNION ALL
          
        
          SELECT
              CONCAT(
                   
    TO_VARCHAR(
                        DATEADD(Day, -1 * 15, CURRENT_DATE),
                        'YYYYMMDD'
                   ),
                   '8'
             )::int                                                            AS order_no,

             UNIFORM(123456, 654321, RANDOM())                                  AS customer_id,

             
                'DELIVERED'                                                    AS status,
             
             DATEADD(Day, -1 * 15, CURRENT_DATE)                    AS created_at,
             current_timestamp                                                 AS changed_at

          
            UNION ALL
          
        
          SELECT
              CONCAT(
                   
    TO_VARCHAR(
                        DATEADD(Day, -1 * 15, CURRENT_DATE),
                        'YYYYMMDD'
                   ),
                   '9'
             )::int                                                            AS order_no,

             UNIFORM(123456, 654321, RANDOM())                                  AS customer_id,

             
                'DELIVERED'                                                    AS status,
             
             DATEADD(Day, -1 * 15, CURRENT_DATE)                    AS created_at,
             current_timestamp                                                 AS changed_at

          
        

        
            UNION ALL
        
    
        
          SELECT
              CONCAT(
                   
    TO_VARCHAR(
                        DATEADD(Day, -1 * 16, CURRENT_DATE),
                        'YYYYMMDD'
                   ),
                   '0'
             )::int                                                            AS order_no,

             UNIFORM(123456, 654321, RANDOM())                                  AS customer_id,

             
                'PENDING'                                                      AS status,
             
             DATEADD(Day, -1 * 16, CURRENT_DATE)                    AS created_at,
             current_timestamp                                                 AS changed_at

          
            UNION ALL
          
        
          SELECT
              CONCAT(
                   
    TO_VARCHAR(
                        DATEADD(Day, -1 * 16, CURRENT_DATE),
                        'YYYYMMDD'
                   ),
                   '1'
             )::int                                                            AS order_no,

             UNIFORM(123456, 654321, RANDOM())                                  AS customer_id,

             
                'DELIVERED'                                                    AS status,
             
             DATEADD(Day, -1 * 16, CURRENT_DATE)                    AS created_at,
             current_timestamp                                                 AS changed_at

          
            UNION ALL
          
        
          SELECT
              CONCAT(
                   
    TO_VARCHAR(
                        DATEADD(Day, -1 * 16, CURRENT_DATE),
                        'YYYYMMDD'
                   ),
                   '2'
             )::int                                                            AS order_no,

             UNIFORM(123456, 654321, RANDOM())                                  AS customer_id,

             
                'DELIVERED'                                                    AS status,
             
             DATEADD(Day, -1 * 16, CURRENT_DATE)                    AS created_at,
             current_timestamp                                                 AS changed_at

          
            UNION ALL
          
        
          SELECT
              CONCAT(
                   
    TO_VARCHAR(
                        DATEADD(Day, -1 * 16, CURRENT_DATE),
                        'YYYYMMDD'
                   ),
                   '3'
             )::int                                                            AS order_no,

             UNIFORM(123456, 654321, RANDOM())                                  AS customer_id,

             
                'DELIVERED'                                                    AS status,
             
             DATEADD(Day, -1 * 16, CURRENT_DATE)                    AS created_at,
             current_timestamp                                                 AS changed_at

          
            UNION ALL
          
        
          SELECT
              CONCAT(
                   
    TO_VARCHAR(
                        DATEADD(Day, -1 * 16, CURRENT_DATE),
                        'YYYYMMDD'
                   ),
                   '4'
             )::int                                                            AS order_no,

             UNIFORM(123456, 654321, RANDOM())                                  AS customer_id,

             
                'DELIVERED'                                                    AS status,
             
             DATEADD(Day, -1 * 16, CURRENT_DATE)                    AS created_at,
             current_timestamp                                                 AS changed_at

          
            UNION ALL
          
        
          SELECT
              CONCAT(
                   
    TO_VARCHAR(
                        DATEADD(Day, -1 * 16, CURRENT_DATE),
                        'YYYYMMDD'
                   ),
                   '5'
             )::int                                                            AS order_no,

             UNIFORM(123456, 654321, RANDOM())                                  AS customer_id,

             
                'DELIVERED'                                                    AS status,
             
             DATEADD(Day, -1 * 16, CURRENT_DATE)                    AS created_at,
             current_timestamp                                                 AS changed_at

          
            UNION ALL
          
        
          SELECT
              CONCAT(
                   
    TO_VARCHAR(
                        DATEADD(Day, -1 * 16, CURRENT_DATE),
                        'YYYYMMDD'
                   ),
                   '6'
             )::int                                                            AS order_no,

             UNIFORM(123456, 654321, RANDOM())                                  AS customer_id,

             
                'DELIVERED'                                                    AS status,
             
             DATEADD(Day, -1 * 16, CURRENT_DATE)                    AS created_at,
             current_timestamp                                                 AS changed_at

          
            UNION ALL
          
        
          SELECT
              CONCAT(
                   
    TO_VARCHAR(
                        DATEADD(Day, -1 * 16, CURRENT_DATE),
                        'YYYYMMDD'
                   ),
                   '7'
             )::int                                                            AS order_no,

             UNIFORM(123456, 654321, RANDOM())                                  AS customer_id,

             
                'DELIVERED'                                                    AS status,
             
             DATEADD(Day, -1 * 16, CURRENT_DATE)                    AS created_at,
             current_timestamp                                                 AS changed_at

          
            UNION ALL
          
        
          SELECT
              CONCAT(
                   
    TO_VARCHAR(
                        DATEADD(Day, -1 * 16, CURRENT_DATE),
                        'YYYYMMDD'
                   ),
                   '8'
             )::int                                                            AS order_no,

             UNIFORM(123456, 654321, RANDOM())                                  AS customer_id,

             
                'DELIVERED'                                                    AS status,
             
             DATEADD(Day, -1 * 16, CURRENT_DATE)                    AS created_at,
             current_timestamp                                                 AS changed_at

          
            UNION ALL
          
        
          SELECT
              CONCAT(
                   
    TO_VARCHAR(
                        DATEADD(Day, -1 * 16, CURRENT_DATE),
                        'YYYYMMDD'
                   ),
                   '9'
             )::int                                                            AS order_no,

             UNIFORM(123456, 654321, RANDOM())                                  AS customer_id,

             
                'DELIVERED'                                                    AS status,
             
             DATEADD(Day, -1 * 16, CURRENT_DATE)                    AS created_at,
             current_timestamp                                                 AS changed_at

          
        

        
            UNION ALL
        
    
        
          SELECT
              CONCAT(
                   
    TO_VARCHAR(
                        DATEADD(Day, -1 * 17, CURRENT_DATE),
                        'YYYYMMDD'
                   ),
                   '0'
             )::int                                                            AS order_no,

             UNIFORM(123456, 654321, RANDOM())                                  AS customer_id,

             
                'PENDING'                                                      AS status,
             
             DATEADD(Day, -1 * 17, CURRENT_DATE)                    AS created_at,
             current_timestamp                                                 AS changed_at

          
            UNION ALL
          
        
          SELECT
              CONCAT(
                   
    TO_VARCHAR(
                        DATEADD(Day, -1 * 17, CURRENT_DATE),
                        'YYYYMMDD'
                   ),
                   '1'
             )::int                                                            AS order_no,

             UNIFORM(123456, 654321, RANDOM())                                  AS customer_id,

             
                'DELIVERED'                                                    AS status,
             
             DATEADD(Day, -1 * 17, CURRENT_DATE)                    AS created_at,
             current_timestamp                                                 AS changed_at

          
            UNION ALL
          
        
          SELECT
              CONCAT(
                   
    TO_VARCHAR(
                        DATEADD(Day, -1 * 17, CURRENT_DATE),
                        'YYYYMMDD'
                   ),
                   '2'
             )::int                                                            AS order_no,

             UNIFORM(123456, 654321, RANDOM())                                  AS customer_id,

             
                'DELIVERED'                                                    AS status,
             
             DATEADD(Day, -1 * 17, CURRENT_DATE)                    AS created_at,
             current_timestamp                                                 AS changed_at

          
            UNION ALL
          
        
          SELECT
              CONCAT(
                   
    TO_VARCHAR(
                        DATEADD(Day, -1 * 17, CURRENT_DATE),
                        'YYYYMMDD'
                   ),
                   '3'
             )::int                                                            AS order_no,

             UNIFORM(123456, 654321, RANDOM())                                  AS customer_id,

             
                'DELIVERED'                                                    AS status,
             
             DATEADD(Day, -1 * 17, CURRENT_DATE)                    AS created_at,
             current_timestamp                                                 AS changed_at

          
            UNION ALL
          
        
          SELECT
              CONCAT(
                   
    TO_VARCHAR(
                        DATEADD(Day, -1 * 17, CURRENT_DATE),
                        'YYYYMMDD'
                   ),
                   '4'
             )::int                                                            AS order_no,

             UNIFORM(123456, 654321, RANDOM())                                  AS customer_id,

             
                'DELIVERED'                                                    AS status,
             
             DATEADD(Day, -1 * 17, CURRENT_DATE)                    AS created_at,
             current_timestamp                                                 AS changed_at

          
            UNION ALL
          
        
          SELECT
              CONCAT(
                   
    TO_VARCHAR(
                        DATEADD(Day, -1 * 17, CURRENT_DATE),
                        'YYYYMMDD'
                   ),
                   '5'
             )::int                                                            AS order_no,

             UNIFORM(123456, 654321, RANDOM())                                  AS customer_id,

             
                'DELIVERED'                                                    AS status,
             
             DATEADD(Day, -1 * 17, CURRENT_DATE)                    AS created_at,
             current_timestamp                                                 AS changed_at

          
            UNION ALL
          
        
          SELECT
              CONCAT(
                   
    TO_VARCHAR(
                        DATEADD(Day, -1 * 17, CURRENT_DATE),
                        'YYYYMMDD'
                   ),
                   '6'
             )::int                                                            AS order_no,

             UNIFORM(123456, 654321, RANDOM())                                  AS customer_id,

             
                'DELIVERED'                                                    AS status,
             
             DATEADD(Day, -1 * 17, CURRENT_DATE)                    AS created_at,
             current_timestamp                                                 AS changed_at

          
            UNION ALL
          
        
          SELECT
              CONCAT(
                   
    TO_VARCHAR(
                        DATEADD(Day, -1 * 17, CURRENT_DATE),
                        'YYYYMMDD'
                   ),
                   '7'
             )::int                                                            AS order_no,

             UNIFORM(123456, 654321, RANDOM())                                  AS customer_id,

             
                'DELIVERED'                                                    AS status,
             
             DATEADD(Day, -1 * 17, CURRENT_DATE)                    AS created_at,
             current_timestamp                                                 AS changed_at

          
            UNION ALL
          
        
          SELECT
              CONCAT(
                   
    TO_VARCHAR(
                        DATEADD(Day, -1 * 17, CURRENT_DATE),
                        'YYYYMMDD'
                   ),
                   '8'
             )::int                                                            AS order_no,

             UNIFORM(123456, 654321, RANDOM())                                  AS customer_id,

             
                'DELIVERED'                                                    AS status,
             
             DATEADD(Day, -1 * 17, CURRENT_DATE)                    AS created_at,
             current_timestamp                                                 AS changed_at

          
            UNION ALL
          
        
          SELECT
              CONCAT(
                   
    TO_VARCHAR(
                        DATEADD(Day, -1 * 17, CURRENT_DATE),
                        'YYYYMMDD'
                   ),
                   '9'
             )::int                                                            AS order_no,

             UNIFORM(123456, 654321, RANDOM())                                  AS customer_id,

             
                'DELIVERED'                                                    AS status,
             
             DATEADD(Day, -1 * 17, CURRENT_DATE)                    AS created_at,
             current_timestamp                                                 AS changed_at

          
        

        
            UNION ALL
        
    
        
          SELECT
              CONCAT(
                   
    TO_VARCHAR(
                        DATEADD(Day, -1 * 18, CURRENT_DATE),
                        'YYYYMMDD'
                   ),
                   '0'
             )::int                                                            AS order_no,

             UNIFORM(123456, 654321, RANDOM())                                  AS customer_id,

             
                'PENDING'                                                      AS status,
             
             DATEADD(Day, -1 * 18, CURRENT_DATE)                    AS created_at,
             current_timestamp                                                 AS changed_at

          
            UNION ALL
          
        
          SELECT
              CONCAT(
                   
    TO_VARCHAR(
                        DATEADD(Day, -1 * 18, CURRENT_DATE),
                        'YYYYMMDD'
                   ),
                   '1'
             )::int                                                            AS order_no,

             UNIFORM(123456, 654321, RANDOM())                                  AS customer_id,

             
                'DELIVERED'                                                    AS status,
             
             DATEADD(Day, -1 * 18, CURRENT_DATE)                    AS created_at,
             current_timestamp                                                 AS changed_at

          
            UNION ALL
          
        
          SELECT
              CONCAT(
                   
    TO_VARCHAR(
                        DATEADD(Day, -1 * 18, CURRENT_DATE),
                        'YYYYMMDD'
                   ),
                   '2'
             )::int                                                            AS order_no,

             UNIFORM(123456, 654321, RANDOM())                                  AS customer_id,

             
                'DELIVERED'                                                    AS status,
             
             DATEADD(Day, -1 * 18, CURRENT_DATE)                    AS created_at,
             current_timestamp                                                 AS changed_at

          
            UNION ALL
          
        
          SELECT
              CONCAT(
                   
    TO_VARCHAR(
                        DATEADD(Day, -1 * 18, CURRENT_DATE),
                        'YYYYMMDD'
                   ),
                   '3'
             )::int                                                            AS order_no,

             UNIFORM(123456, 654321, RANDOM())                                  AS customer_id,

             
                'DELIVERED'                                                    AS status,
             
             DATEADD(Day, -1 * 18, CURRENT_DATE)                    AS created_at,
             current_timestamp                                                 AS changed_at

          
            UNION ALL
          
        
          SELECT
              CONCAT(
                   
    TO_VARCHAR(
                        DATEADD(Day, -1 * 18, CURRENT_DATE),
                        'YYYYMMDD'
                   ),
                   '4'
             )::int                                                            AS order_no,

             UNIFORM(123456, 654321, RANDOM())                                  AS customer_id,

             
                'DELIVERED'                                                    AS status,
             
             DATEADD(Day, -1 * 18, CURRENT_DATE)                    AS created_at,
             current_timestamp                                                 AS changed_at

          
            UNION ALL
          
        
          SELECT
              CONCAT(
                   
    TO_VARCHAR(
                        DATEADD(Day, -1 * 18, CURRENT_DATE),
                        'YYYYMMDD'
                   ),
                   '5'
             )::int                                                            AS order_no,

             UNIFORM(123456, 654321, RANDOM())                                  AS customer_id,

             
                'DELIVERED'                                                    AS status,
             
             DATEADD(Day, -1 * 18, CURRENT_DATE)                    AS created_at,
             current_timestamp                                                 AS changed_at

          
            UNION ALL
          
        
          SELECT
              CONCAT(
                   
    TO_VARCHAR(
                        DATEADD(Day, -1 * 18, CURRENT_DATE),
                        'YYYYMMDD'
                   ),
                   '6'
             )::int                                                            AS order_no,

             UNIFORM(123456, 654321, RANDOM())                                  AS customer_id,

             
                'DELIVERED'                                                    AS status,
             
             DATEADD(Day, -1 * 18, CURRENT_DATE)                    AS created_at,
             current_timestamp                                                 AS changed_at

          
            UNION ALL
          
        
          SELECT
              CONCAT(
                   
    TO_VARCHAR(
                        DATEADD(Day, -1 * 18, CURRENT_DATE),
                        'YYYYMMDD'
                   ),
                   '7'
             )::int                                                            AS order_no,

             UNIFORM(123456, 654321, RANDOM())                                  AS customer_id,

             
                'DELIVERED'                                                    AS status,
             
             DATEADD(Day, -1 * 18, CURRENT_DATE)                    AS created_at,
             current_timestamp                                                 AS changed_at

          
            UNION ALL
          
        
          SELECT
              CONCAT(
                   
    TO_VARCHAR(
                        DATEADD(Day, -1 * 18, CURRENT_DATE),
                        'YYYYMMDD'
                   ),
                   '8'
             )::int                                                            AS order_no,

             UNIFORM(123456, 654321, RANDOM())                                  AS customer_id,

             
                'DELIVERED'                                                    AS status,
             
             DATEADD(Day, -1 * 18, CURRENT_DATE)                    AS created_at,
             current_timestamp                                                 AS changed_at

          
            UNION ALL
          
        
          SELECT
              CONCAT(
                   
    TO_VARCHAR(
                        DATEADD(Day, -1 * 18, CURRENT_DATE),
                        'YYYYMMDD'
                   ),
                   '9'
             )::int                                                            AS order_no,

             UNIFORM(123456, 654321, RANDOM())                                  AS customer_id,

             
                'DELIVERED'                                                    AS status,
             
             DATEADD(Day, -1 * 18, CURRENT_DATE)                    AS created_at,
             current_timestamp                                                 AS changed_at

          
        

        
            UNION ALL
        
    
        
          SELECT
              CONCAT(
                   
    TO_VARCHAR(
                        DATEADD(Day, -1 * 19, CURRENT_DATE),
                        'YYYYMMDD'
                   ),
                   '0'
             )::int                                                            AS order_no,

             UNIFORM(123456, 654321, RANDOM())                                  AS customer_id,

             
                'PENDING'                                                      AS status,
             
             DATEADD(Day, -1 * 19, CURRENT_DATE)                    AS created_at,
             current_timestamp                                                 AS changed_at

          
            UNION ALL
          
        
          SELECT
              CONCAT(
                   
    TO_VARCHAR(
                        DATEADD(Day, -1 * 19, CURRENT_DATE),
                        'YYYYMMDD'
                   ),
                   '1'
             )::int                                                            AS order_no,

             UNIFORM(123456, 654321, RANDOM())                                  AS customer_id,

             
                'DELIVERED'                                                    AS status,
             
             DATEADD(Day, -1 * 19, CURRENT_DATE)                    AS created_at,
             current_timestamp                                                 AS changed_at

          
            UNION ALL
          
        
          SELECT
              CONCAT(
                   
    TO_VARCHAR(
                        DATEADD(Day, -1 * 19, CURRENT_DATE),
                        'YYYYMMDD'
                   ),
                   '2'
             )::int                                                            AS order_no,

             UNIFORM(123456, 654321, RANDOM())                                  AS customer_id,

             
                'DELIVERED'                                                    AS status,
             
             DATEADD(Day, -1 * 19, CURRENT_DATE)                    AS created_at,
             current_timestamp                                                 AS changed_at

          
            UNION ALL
          
        
          SELECT
              CONCAT(
                   
    TO_VARCHAR(
                        DATEADD(Day, -1 * 19, CURRENT_DATE),
                        'YYYYMMDD'
                   ),
                   '3'
             )::int                                                            AS order_no,

             UNIFORM(123456, 654321, RANDOM())                                  AS customer_id,

             
                'DELIVERED'                                                    AS status,
             
             DATEADD(Day, -1 * 19, CURRENT_DATE)                    AS created_at,
             current_timestamp                                                 AS changed_at

          
            UNION ALL
          
        
          SELECT
              CONCAT(
                   
    TO_VARCHAR(
                        DATEADD(Day, -1 * 19, CURRENT_DATE),
                        'YYYYMMDD'
                   ),
                   '4'
             )::int                                                            AS order_no,

             UNIFORM(123456, 654321, RANDOM())                                  AS customer_id,

             
                'DELIVERED'                                                    AS status,
             
             DATEADD(Day, -1 * 19, CURRENT_DATE)                    AS created_at,
             current_timestamp                                                 AS changed_at

          
            UNION ALL
          
        
          SELECT
              CONCAT(
                   
    TO_VARCHAR(
                        DATEADD(Day, -1 * 19, CURRENT_DATE),
                        'YYYYMMDD'
                   ),
                   '5'
             )::int                                                            AS order_no,

             UNIFORM(123456, 654321, RANDOM())                                  AS customer_id,

             
                'DELIVERED'                                                    AS status,
             
             DATEADD(Day, -1 * 19, CURRENT_DATE)                    AS created_at,
             current_timestamp                                                 AS changed_at

          
            UNION ALL
          
        
          SELECT
              CONCAT(
                   
    TO_VARCHAR(
                        DATEADD(Day, -1 * 19, CURRENT_DATE),
                        'YYYYMMDD'
                   ),
                   '6'
             )::int                                                            AS order_no,

             UNIFORM(123456, 654321, RANDOM())                                  AS customer_id,

             
                'DELIVERED'                                                    AS status,
             
             DATEADD(Day, -1 * 19, CURRENT_DATE)                    AS created_at,
             current_timestamp                                                 AS changed_at

          
            UNION ALL
          
        
          SELECT
              CONCAT(
                   
    TO_VARCHAR(
                        DATEADD(Day, -1 * 19, CURRENT_DATE),
                        'YYYYMMDD'
                   ),
                   '7'
             )::int                                                            AS order_no,

             UNIFORM(123456, 654321, RANDOM())                                  AS customer_id,

             
                'DELIVERED'                                                    AS status,
             
             DATEADD(Day, -1 * 19, CURRENT_DATE)                    AS created_at,
             current_timestamp                                                 AS changed_at

          
            UNION ALL
          
        
          SELECT
              CONCAT(
                   
    TO_VARCHAR(
                        DATEADD(Day, -1 * 19, CURRENT_DATE),
                        'YYYYMMDD'
                   ),
                   '8'
             )::int                                                            AS order_no,

             UNIFORM(123456, 654321, RANDOM())                                  AS customer_id,

             
                'DELIVERED'                                                    AS status,
             
             DATEADD(Day, -1 * 19, CURRENT_DATE)                    AS created_at,
             current_timestamp                                                 AS changed_at

          
            UNION ALL
          
        
          SELECT
              CONCAT(
                   
    TO_VARCHAR(
                        DATEADD(Day, -1 * 19, CURRENT_DATE),
                        'YYYYMMDD'
                   ),
                   '9'
             )::int                                                            AS order_no,

             UNIFORM(123456, 654321, RANDOM())                                  AS customer_id,

             
                'DELIVERED'                                                    AS status,
             
             DATEADD(Day, -1 * 19, CURRENT_DATE)                    AS created_at,
             current_timestamp                                                 AS changed_at

          
        

        
            UNION ALL
        
    
        
          SELECT
              CONCAT(
                   
    TO_VARCHAR(
                        DATEADD(Day, -1 * 20, CURRENT_DATE),
                        'YYYYMMDD'
                   ),
                   '0'
             )::int                                                            AS order_no,

             UNIFORM(123456, 654321, RANDOM())                                  AS customer_id,

             
                'PENDING'                                                      AS status,
             
             DATEADD(Day, -1 * 20, CURRENT_DATE)                    AS created_at,
             current_timestamp                                                 AS changed_at

          
            UNION ALL
          
        
          SELECT
              CONCAT(
                   
    TO_VARCHAR(
                        DATEADD(Day, -1 * 20, CURRENT_DATE),
                        'YYYYMMDD'
                   ),
                   '1'
             )::int                                                            AS order_no,

             UNIFORM(123456, 654321, RANDOM())                                  AS customer_id,

             
                'DELIVERED'                                                    AS status,
             
             DATEADD(Day, -1 * 20, CURRENT_DATE)                    AS created_at,
             current_timestamp                                                 AS changed_at

          
            UNION ALL
          
        
          SELECT
              CONCAT(
                   
    TO_VARCHAR(
                        DATEADD(Day, -1 * 20, CURRENT_DATE),
                        'YYYYMMDD'
                   ),
                   '2'
             )::int                                                            AS order_no,

             UNIFORM(123456, 654321, RANDOM())                                  AS customer_id,

             
                'DELIVERED'                                                    AS status,
             
             DATEADD(Day, -1 * 20, CURRENT_DATE)                    AS created_at,
             current_timestamp                                                 AS changed_at

          
            UNION ALL
          
        
          SELECT
              CONCAT(
                   
    TO_VARCHAR(
                        DATEADD(Day, -1 * 20, CURRENT_DATE),
                        'YYYYMMDD'
                   ),
                   '3'
             )::int                                                            AS order_no,

             UNIFORM(123456, 654321, RANDOM())                                  AS customer_id,

             
                'DELIVERED'                                                    AS status,
             
             DATEADD(Day, -1 * 20, CURRENT_DATE)                    AS created_at,
             current_timestamp                                                 AS changed_at

          
            UNION ALL
          
        
          SELECT
              CONCAT(
                   
    TO_VARCHAR(
                        DATEADD(Day, -1 * 20, CURRENT_DATE),
                        'YYYYMMDD'
                   ),
                   '4'
             )::int                                                            AS order_no,

             UNIFORM(123456, 654321, RANDOM())                                  AS customer_id,

             
                'DELIVERED'                                                    AS status,
             
             DATEADD(Day, -1 * 20, CURRENT_DATE)                    AS created_at,
             current_timestamp                                                 AS changed_at

          
            UNION ALL
          
        
          SELECT
              CONCAT(
                   
    TO_VARCHAR(
                        DATEADD(Day, -1 * 20, CURRENT_DATE),
                        'YYYYMMDD'
                   ),
                   '5'
             )::int                                                            AS order_no,

             UNIFORM(123456, 654321, RANDOM())                                  AS customer_id,

             
                'DELIVERED'                                                    AS status,
             
             DATEADD(Day, -1 * 20, CURRENT_DATE)                    AS created_at,
             current_timestamp                                                 AS changed_at

          
            UNION ALL
          
        
          SELECT
              CONCAT(
                   
    TO_VARCHAR(
                        DATEADD(Day, -1 * 20, CURRENT_DATE),
                        'YYYYMMDD'
                   ),
                   '6'
             )::int                                                            AS order_no,

             UNIFORM(123456, 654321, RANDOM())                                  AS customer_id,

             
                'DELIVERED'                                                    AS status,
             
             DATEADD(Day, -1 * 20, CURRENT_DATE)                    AS created_at,
             current_timestamp                                                 AS changed_at

          
            UNION ALL
          
        
          SELECT
              CONCAT(
                   
    TO_VARCHAR(
                        DATEADD(Day, -1 * 20, CURRENT_DATE),
                        'YYYYMMDD'
                   ),
                   '7'
             )::int                                                            AS order_no,

             UNIFORM(123456, 654321, RANDOM())                                  AS customer_id,

             
                'DELIVERED'                                                    AS status,
             
             DATEADD(Day, -1 * 20, CURRENT_DATE)                    AS created_at,
             current_timestamp                                                 AS changed_at

          
            UNION ALL
          
        
          SELECT
              CONCAT(
                   
    TO_VARCHAR(
                        DATEADD(Day, -1 * 20, CURRENT_DATE),
                        'YYYYMMDD'
                   ),
                   '8'
             )::int                                                            AS order_no,

             UNIFORM(123456, 654321, RANDOM())                                  AS customer_id,

             
                'DELIVERED'                                                    AS status,
             
             DATEADD(Day, -1 * 20, CURRENT_DATE)                    AS created_at,
             current_timestamp                                                 AS changed_at

          
            UNION ALL
          
        
          SELECT
              CONCAT(
                   
    TO_VARCHAR(
                        DATEADD(Day, -1 * 20, CURRENT_DATE),
                        'YYYYMMDD'
                   ),
                   '9'
             )::int                                                            AS order_no,

             UNIFORM(123456, 654321, RANDOM())                                  AS customer_id,

             
                'DELIVERED'                                                    AS status,
             
             DATEADD(Day, -1 * 20, CURRENT_DATE)                    AS created_at,
             current_timestamp                                                 AS changed_at

          
        

        
            UNION ALL
        
    
        
          SELECT
              CONCAT(
                   
    TO_VARCHAR(
                        DATEADD(Day, -1 * 21, CURRENT_DATE),
                        'YYYYMMDD'
                   ),
                   '0'
             )::int                                                            AS order_no,

             UNIFORM(123456, 654321, RANDOM())                                  AS customer_id,

             
                'PENDING'                                                      AS status,
             
             DATEADD(Day, -1 * 21, CURRENT_DATE)                    AS created_at,
             current_timestamp                                                 AS changed_at

          
            UNION ALL
          
        
          SELECT
              CONCAT(
                   
    TO_VARCHAR(
                        DATEADD(Day, -1 * 21, CURRENT_DATE),
                        'YYYYMMDD'
                   ),
                   '1'
             )::int                                                            AS order_no,

             UNIFORM(123456, 654321, RANDOM())                                  AS customer_id,

             
                'DELIVERED'                                                    AS status,
             
             DATEADD(Day, -1 * 21, CURRENT_DATE)                    AS created_at,
             current_timestamp                                                 AS changed_at

          
            UNION ALL
          
        
          SELECT
              CONCAT(
                   
    TO_VARCHAR(
                        DATEADD(Day, -1 * 21, CURRENT_DATE),
                        'YYYYMMDD'
                   ),
                   '2'
             )::int                                                            AS order_no,

             UNIFORM(123456, 654321, RANDOM())                                  AS customer_id,

             
                'DELIVERED'                                                    AS status,
             
             DATEADD(Day, -1 * 21, CURRENT_DATE)                    AS created_at,
             current_timestamp                                                 AS changed_at

          
            UNION ALL
          
        
          SELECT
              CONCAT(
                   
    TO_VARCHAR(
                        DATEADD(Day, -1 * 21, CURRENT_DATE),
                        'YYYYMMDD'
                   ),
                   '3'
             )::int                                                            AS order_no,

             UNIFORM(123456, 654321, RANDOM())                                  AS customer_id,

             
                'DELIVERED'                                                    AS status,
             
             DATEADD(Day, -1 * 21, CURRENT_DATE)                    AS created_at,
             current_timestamp                                                 AS changed_at

          
            UNION ALL
          
        
          SELECT
              CONCAT(
                   
    TO_VARCHAR(
                        DATEADD(Day, -1 * 21, CURRENT_DATE),
                        'YYYYMMDD'
                   ),
                   '4'
             )::int                                                            AS order_no,

             UNIFORM(123456, 654321, RANDOM())                                  AS customer_id,

             
                'DELIVERED'                                                    AS status,
             
             DATEADD(Day, -1 * 21, CURRENT_DATE)                    AS created_at,
             current_timestamp                                                 AS changed_at

          
            UNION ALL
          
        
          SELECT
              CONCAT(
                   
    TO_VARCHAR(
                        DATEADD(Day, -1 * 21, CURRENT_DATE),
                        'YYYYMMDD'
                   ),
                   '5'
             )::int                                                            AS order_no,

             UNIFORM(123456, 654321, RANDOM())                                  AS customer_id,

             
                'DELIVERED'                                                    AS status,
             
             DATEADD(Day, -1 * 21, CURRENT_DATE)                    AS created_at,
             current_timestamp                                                 AS changed_at

          
            UNION ALL
          
        
          SELECT
              CONCAT(
                   
    TO_VARCHAR(
                        DATEADD(Day, -1 * 21, CURRENT_DATE),
                        'YYYYMMDD'
                   ),
                   '6'
             )::int                                                            AS order_no,

             UNIFORM(123456, 654321, RANDOM())                                  AS customer_id,

             
                'DELIVERED'                                                    AS status,
             
             DATEADD(Day, -1 * 21, CURRENT_DATE)                    AS created_at,
             current_timestamp                                                 AS changed_at

          
            UNION ALL
          
        
          SELECT
              CONCAT(
                   
    TO_VARCHAR(
                        DATEADD(Day, -1 * 21, CURRENT_DATE),
                        'YYYYMMDD'
                   ),
                   '7'
             )::int                                                            AS order_no,

             UNIFORM(123456, 654321, RANDOM())                                  AS customer_id,

             
                'DELIVERED'                                                    AS status,
             
             DATEADD(Day, -1 * 21, CURRENT_DATE)                    AS created_at,
             current_timestamp                                                 AS changed_at

          
            UNION ALL
          
        
          SELECT
              CONCAT(
                   
    TO_VARCHAR(
                        DATEADD(Day, -1 * 21, CURRENT_DATE),
                        'YYYYMMDD'
                   ),
                   '8'
             )::int                                                            AS order_no,

             UNIFORM(123456, 654321, RANDOM())                                  AS customer_id,

             
                'DELIVERED'                                                    AS status,
             
             DATEADD(Day, -1 * 21, CURRENT_DATE)                    AS created_at,
             current_timestamp                                                 AS changed_at

          
            UNION ALL
          
        
          SELECT
              CONCAT(
                   
    TO_VARCHAR(
                        DATEADD(Day, -1 * 21, CURRENT_DATE),
                        'YYYYMMDD'
                   ),
                   '9'
             )::int                                                            AS order_no,

             UNIFORM(123456, 654321, RANDOM())                                  AS customer_id,

             
                'DELIVERED'                                                    AS status,
             
             DATEADD(Day, -1 * 21, CURRENT_DATE)                    AS created_at,
             current_timestamp                                                 AS changed_at

          
        

        
            UNION ALL
        
    
        
          SELECT
              CONCAT(
                   
    TO_VARCHAR(
                        DATEADD(Day, -1 * 22, CURRENT_DATE),
                        'YYYYMMDD'
                   ),
                   '0'
             )::int                                                            AS order_no,

             UNIFORM(123456, 654321, RANDOM())                                  AS customer_id,

             
                'PENDING'                                                      AS status,
             
             DATEADD(Day, -1 * 22, CURRENT_DATE)                    AS created_at,
             current_timestamp                                                 AS changed_at

          
            UNION ALL
          
        
          SELECT
              CONCAT(
                   
    TO_VARCHAR(
                        DATEADD(Day, -1 * 22, CURRENT_DATE),
                        'YYYYMMDD'
                   ),
                   '1'
             )::int                                                            AS order_no,

             UNIFORM(123456, 654321, RANDOM())                                  AS customer_id,

             
                'DELIVERED'                                                    AS status,
             
             DATEADD(Day, -1 * 22, CURRENT_DATE)                    AS created_at,
             current_timestamp                                                 AS changed_at

          
            UNION ALL
          
        
          SELECT
              CONCAT(
                   
    TO_VARCHAR(
                        DATEADD(Day, -1 * 22, CURRENT_DATE),
                        'YYYYMMDD'
                   ),
                   '2'
             )::int                                                            AS order_no,

             UNIFORM(123456, 654321, RANDOM())                                  AS customer_id,

             
                'DELIVERED'                                                    AS status,
             
             DATEADD(Day, -1 * 22, CURRENT_DATE)                    AS created_at,
             current_timestamp                                                 AS changed_at

          
            UNION ALL
          
        
          SELECT
              CONCAT(
                   
    TO_VARCHAR(
                        DATEADD(Day, -1 * 22, CURRENT_DATE),
                        'YYYYMMDD'
                   ),
                   '3'
             )::int                                                            AS order_no,

             UNIFORM(123456, 654321, RANDOM())                                  AS customer_id,

             
                'DELIVERED'                                                    AS status,
             
             DATEADD(Day, -1 * 22, CURRENT_DATE)                    AS created_at,
             current_timestamp                                                 AS changed_at

          
            UNION ALL
          
        
          SELECT
              CONCAT(
                   
    TO_VARCHAR(
                        DATEADD(Day, -1 * 22, CURRENT_DATE),
                        'YYYYMMDD'
                   ),
                   '4'
             )::int                                                            AS order_no,

             UNIFORM(123456, 654321, RANDOM())                                  AS customer_id,

             
                'DELIVERED'                                                    AS status,
             
             DATEADD(Day, -1 * 22, CURRENT_DATE)                    AS created_at,
             current_timestamp                                                 AS changed_at

          
            UNION ALL
          
        
          SELECT
              CONCAT(
                   
    TO_VARCHAR(
                        DATEADD(Day, -1 * 22, CURRENT_DATE),
                        'YYYYMMDD'
                   ),
                   '5'
             )::int                                                            AS order_no,

             UNIFORM(123456, 654321, RANDOM())                                  AS customer_id,

             
                'DELIVERED'                                                    AS status,
             
             DATEADD(Day, -1 * 22, CURRENT_DATE)                    AS created_at,
             current_timestamp                                                 AS changed_at

          
            UNION ALL
          
        
          SELECT
              CONCAT(
                   
    TO_VARCHAR(
                        DATEADD(Day, -1 * 22, CURRENT_DATE),
                        'YYYYMMDD'
                   ),
                   '6'
             )::int                                                            AS order_no,

             UNIFORM(123456, 654321, RANDOM())                                  AS customer_id,

             
                'DELIVERED'                                                    AS status,
             
             DATEADD(Day, -1 * 22, CURRENT_DATE)                    AS created_at,
             current_timestamp                                                 AS changed_at

          
            UNION ALL
          
        
          SELECT
              CONCAT(
                   
    TO_VARCHAR(
                        DATEADD(Day, -1 * 22, CURRENT_DATE),
                        'YYYYMMDD'
                   ),
                   '7'
             )::int                                                            AS order_no,

             UNIFORM(123456, 654321, RANDOM())                                  AS customer_id,

             
                'DELIVERED'                                                    AS status,
             
             DATEADD(Day, -1 * 22, CURRENT_DATE)                    AS created_at,
             current_timestamp                                                 AS changed_at

          
            UNION ALL
          
        
          SELECT
              CONCAT(
                   
    TO_VARCHAR(
                        DATEADD(Day, -1 * 22, CURRENT_DATE),
                        'YYYYMMDD'
                   ),
                   '8'
             )::int                                                            AS order_no,

             UNIFORM(123456, 654321, RANDOM())                                  AS customer_id,

             
                'DELIVERED'                                                    AS status,
             
             DATEADD(Day, -1 * 22, CURRENT_DATE)                    AS created_at,
             current_timestamp                                                 AS changed_at

          
            UNION ALL
          
        
          SELECT
              CONCAT(
                   
    TO_VARCHAR(
                        DATEADD(Day, -1 * 22, CURRENT_DATE),
                        'YYYYMMDD'
                   ),
                   '9'
             )::int                                                            AS order_no,

             UNIFORM(123456, 654321, RANDOM())                                  AS customer_id,

             
                'DELIVERED'                                                    AS status,
             
             DATEADD(Day, -1 * 22, CURRENT_DATE)                    AS created_at,
             current_timestamp                                                 AS changed_at

          
        

        
            UNION ALL
        
    
        
          SELECT
              CONCAT(
                   
    TO_VARCHAR(
                        DATEADD(Day, -1 * 23, CURRENT_DATE),
                        'YYYYMMDD'
                   ),
                   '0'
             )::int                                                            AS order_no,

             UNIFORM(123456, 654321, RANDOM())                                  AS customer_id,

             
                'PENDING'                                                      AS status,
             
             DATEADD(Day, -1 * 23, CURRENT_DATE)                    AS created_at,
             current_timestamp                                                 AS changed_at

          
            UNION ALL
          
        
          SELECT
              CONCAT(
                   
    TO_VARCHAR(
                        DATEADD(Day, -1 * 23, CURRENT_DATE),
                        'YYYYMMDD'
                   ),
                   '1'
             )::int                                                            AS order_no,

             UNIFORM(123456, 654321, RANDOM())                                  AS customer_id,

             
                'DELIVERED'                                                    AS status,
             
             DATEADD(Day, -1 * 23, CURRENT_DATE)                    AS created_at,
             current_timestamp                                                 AS changed_at

          
            UNION ALL
          
        
          SELECT
              CONCAT(
                   
    TO_VARCHAR(
                        DATEADD(Day, -1 * 23, CURRENT_DATE),
                        'YYYYMMDD'
                   ),
                   '2'
             )::int                                                            AS order_no,

             UNIFORM(123456, 654321, RANDOM())                                  AS customer_id,

             
                'DELIVERED'                                                    AS status,
             
             DATEADD(Day, -1 * 23, CURRENT_DATE)                    AS created_at,
             current_timestamp                                                 AS changed_at

          
            UNION ALL
          
        
          SELECT
              CONCAT(
                   
    TO_VARCHAR(
                        DATEADD(Day, -1 * 23, CURRENT_DATE),
                        'YYYYMMDD'
                   ),
                   '3'
             )::int                                                            AS order_no,

             UNIFORM(123456, 654321, RANDOM())                                  AS customer_id,

             
                'DELIVERED'                                                    AS status,
             
             DATEADD(Day, -1 * 23, CURRENT_DATE)                    AS created_at,
             current_timestamp                                                 AS changed_at

          
            UNION ALL
          
        
          SELECT
              CONCAT(
                   
    TO_VARCHAR(
                        DATEADD(Day, -1 * 23, CURRENT_DATE),
                        'YYYYMMDD'
                   ),
                   '4'
             )::int                                                            AS order_no,

             UNIFORM(123456, 654321, RANDOM())                                  AS customer_id,

             
                'DELIVERED'                                                    AS status,
             
             DATEADD(Day, -1 * 23, CURRENT_DATE)                    AS created_at,
             current_timestamp                                                 AS changed_at

          
            UNION ALL
          
        
          SELECT
              CONCAT(
                   
    TO_VARCHAR(
                        DATEADD(Day, -1 * 23, CURRENT_DATE),
                        'YYYYMMDD'
                   ),
                   '5'
             )::int                                                            AS order_no,

             UNIFORM(123456, 654321, RANDOM())                                  AS customer_id,

             
                'DELIVERED'                                                    AS status,
             
             DATEADD(Day, -1 * 23, CURRENT_DATE)                    AS created_at,
             current_timestamp                                                 AS changed_at

          
            UNION ALL
          
        
          SELECT
              CONCAT(
                   
    TO_VARCHAR(
                        DATEADD(Day, -1 * 23, CURRENT_DATE),
                        'YYYYMMDD'
                   ),
                   '6'
             )::int                                                            AS order_no,

             UNIFORM(123456, 654321, RANDOM())                                  AS customer_id,

             
                'DELIVERED'                                                    AS status,
             
             DATEADD(Day, -1 * 23, CURRENT_DATE)                    AS created_at,
             current_timestamp                                                 AS changed_at

          
            UNION ALL
          
        
          SELECT
              CONCAT(
                   
    TO_VARCHAR(
                        DATEADD(Day, -1 * 23, CURRENT_DATE),
                        'YYYYMMDD'
                   ),
                   '7'
             )::int                                                            AS order_no,

             UNIFORM(123456, 654321, RANDOM())                                  AS customer_id,

             
                'DELIVERED'                                                    AS status,
             
             DATEADD(Day, -1 * 23, CURRENT_DATE)                    AS created_at,
             current_timestamp                                                 AS changed_at

          
            UNION ALL
          
        
          SELECT
              CONCAT(
                   
    TO_VARCHAR(
                        DATEADD(Day, -1 * 23, CURRENT_DATE),
                        'YYYYMMDD'
                   ),
                   '8'
             )::int                                                            AS order_no,

             UNIFORM(123456, 654321, RANDOM())                                  AS customer_id,

             
                'DELIVERED'                                                    AS status,
             
             DATEADD(Day, -1 * 23, CURRENT_DATE)                    AS created_at,
             current_timestamp                                                 AS changed_at

          
            UNION ALL
          
        
          SELECT
              CONCAT(
                   
    TO_VARCHAR(
                        DATEADD(Day, -1 * 23, CURRENT_DATE),
                        'YYYYMMDD'
                   ),
                   '9'
             )::int                                                            AS order_no,

             UNIFORM(123456, 654321, RANDOM())                                  AS customer_id,

             
                'DELIVERED'                                                    AS status,
             
             DATEADD(Day, -1 * 23, CURRENT_DATE)                    AS created_at,
             current_timestamp                                                 AS changed_at

          
        

        
            UNION ALL
        
    
        
          SELECT
              CONCAT(
                   
    TO_VARCHAR(
                        DATEADD(Day, -1 * 24, CURRENT_DATE),
                        'YYYYMMDD'
                   ),
                   '0'
             )::int                                                            AS order_no,

             UNIFORM(123456, 654321, RANDOM())                                  AS customer_id,

             
                'PENDING'                                                      AS status,
             
             DATEADD(Day, -1 * 24, CURRENT_DATE)                    AS created_at,
             current_timestamp                                                 AS changed_at

          
            UNION ALL
          
        
          SELECT
              CONCAT(
                   
    TO_VARCHAR(
                        DATEADD(Day, -1 * 24, CURRENT_DATE),
                        'YYYYMMDD'
                   ),
                   '1'
             )::int                                                            AS order_no,

             UNIFORM(123456, 654321, RANDOM())                                  AS customer_id,

             
                'DELIVERED'                                                    AS status,
             
             DATEADD(Day, -1 * 24, CURRENT_DATE)                    AS created_at,
             current_timestamp                                                 AS changed_at

          
            UNION ALL
          
        
          SELECT
              CONCAT(
                   
    TO_VARCHAR(
                        DATEADD(Day, -1 * 24, CURRENT_DATE),
                        'YYYYMMDD'
                   ),
                   '2'
             )::int                                                            AS order_no,

             UNIFORM(123456, 654321, RANDOM())                                  AS customer_id,

             
                'DELIVERED'                                                    AS status,
             
             DATEADD(Day, -1 * 24, CURRENT_DATE)                    AS created_at,
             current_timestamp                                                 AS changed_at

          
            UNION ALL
          
        
          SELECT
              CONCAT(
                   
    TO_VARCHAR(
                        DATEADD(Day, -1 * 24, CURRENT_DATE),
                        'YYYYMMDD'
                   ),
                   '3'
             )::int                                                            AS order_no,

             UNIFORM(123456, 654321, RANDOM())                                  AS customer_id,

             
                'DELIVERED'                                                    AS status,
             
             DATEADD(Day, -1 * 24, CURRENT_DATE)                    AS created_at,
             current_timestamp                                                 AS changed_at

          
            UNION ALL
          
        
          SELECT
              CONCAT(
                   
    TO_VARCHAR(
                        DATEADD(Day, -1 * 24, CURRENT_DATE),
                        'YYYYMMDD'
                   ),
                   '4'
             )::int                                                            AS order_no,

             UNIFORM(123456, 654321, RANDOM())                                  AS customer_id,

             
                'DELIVERED'                                                    AS status,
             
             DATEADD(Day, -1 * 24, CURRENT_DATE)                    AS created_at,
             current_timestamp                                                 AS changed_at

          
            UNION ALL
          
        
          SELECT
              CONCAT(
                   
    TO_VARCHAR(
                        DATEADD(Day, -1 * 24, CURRENT_DATE),
                        'YYYYMMDD'
                   ),
                   '5'
             )::int                                                            AS order_no,

             UNIFORM(123456, 654321, RANDOM())                                  AS customer_id,

             
                'DELIVERED'                                                    AS status,
             
             DATEADD(Day, -1 * 24, CURRENT_DATE)                    AS created_at,
             current_timestamp                                                 AS changed_at

          
            UNION ALL
          
        
          SELECT
              CONCAT(
                   
    TO_VARCHAR(
                        DATEADD(Day, -1 * 24, CURRENT_DATE),
                        'YYYYMMDD'
                   ),
                   '6'
             )::int                                                            AS order_no,

             UNIFORM(123456, 654321, RANDOM())                                  AS customer_id,

             
                'DELIVERED'                                                    AS status,
             
             DATEADD(Day, -1 * 24, CURRENT_DATE)                    AS created_at,
             current_timestamp                                                 AS changed_at

          
            UNION ALL
          
        
          SELECT
              CONCAT(
                   
    TO_VARCHAR(
                        DATEADD(Day, -1 * 24, CURRENT_DATE),
                        'YYYYMMDD'
                   ),
                   '7'
             )::int                                                            AS order_no,

             UNIFORM(123456, 654321, RANDOM())                                  AS customer_id,

             
                'DELIVERED'                                                    AS status,
             
             DATEADD(Day, -1 * 24, CURRENT_DATE)                    AS created_at,
             current_timestamp                                                 AS changed_at

          
            UNION ALL
          
        
          SELECT
              CONCAT(
                   
    TO_VARCHAR(
                        DATEADD(Day, -1 * 24, CURRENT_DATE),
                        'YYYYMMDD'
                   ),
                   '8'
             )::int                                                            AS order_no,

             UNIFORM(123456, 654321, RANDOM())                                  AS customer_id,

             
                'DELIVERED'                                                    AS status,
             
             DATEADD(Day, -1 * 24, CURRENT_DATE)                    AS created_at,
             current_timestamp                                                 AS changed_at

          
            UNION ALL
          
        
          SELECT
              CONCAT(
                   
    TO_VARCHAR(
                        DATEADD(Day, -1 * 24, CURRENT_DATE),
                        'YYYYMMDD'
                   ),
                   '9'
             )::int                                                            AS order_no,

             UNIFORM(123456, 654321, RANDOM())                                  AS customer_id,

             
                'DELIVERED'                                                    AS status,
             
             DATEADD(Day, -1 * 24, CURRENT_DATE)                    AS created_at,
             current_timestamp                                                 AS changed_at

          
        

        
            UNION ALL
        
    
        
          SELECT
              CONCAT(
                   
    TO_VARCHAR(
                        DATEADD(Day, -1 * 25, CURRENT_DATE),
                        'YYYYMMDD'
                   ),
                   '0'
             )::int                                                            AS order_no,

             UNIFORM(123456, 654321, RANDOM())                                  AS customer_id,

             
                'PENDING'                                                      AS status,
             
             DATEADD(Day, -1 * 25, CURRENT_DATE)                    AS created_at,
             current_timestamp                                                 AS changed_at

          
            UNION ALL
          
        
          SELECT
              CONCAT(
                   
    TO_VARCHAR(
                        DATEADD(Day, -1 * 25, CURRENT_DATE),
                        'YYYYMMDD'
                   ),
                   '1'
             )::int                                                            AS order_no,

             UNIFORM(123456, 654321, RANDOM())                                  AS customer_id,

             
                'DELIVERED'                                                    AS status,
             
             DATEADD(Day, -1 * 25, CURRENT_DATE)                    AS created_at,
             current_timestamp                                                 AS changed_at

          
            UNION ALL
          
        
          SELECT
              CONCAT(
                   
    TO_VARCHAR(
                        DATEADD(Day, -1 * 25, CURRENT_DATE),
                        'YYYYMMDD'
                   ),
                   '2'
             )::int                                                            AS order_no,

             UNIFORM(123456, 654321, RANDOM())                                  AS customer_id,

             
                'DELIVERED'                                                    AS status,
             
             DATEADD(Day, -1 * 25, CURRENT_DATE)                    AS created_at,
             current_timestamp                                                 AS changed_at

          
            UNION ALL
          
        
          SELECT
              CONCAT(
                   
    TO_VARCHAR(
                        DATEADD(Day, -1 * 25, CURRENT_DATE),
                        'YYYYMMDD'
                   ),
                   '3'
             )::int                                                            AS order_no,

             UNIFORM(123456, 654321, RANDOM())                                  AS customer_id,

             
                'DELIVERED'                                                    AS status,
             
             DATEADD(Day, -1 * 25, CURRENT_DATE)                    AS created_at,
             current_timestamp                                                 AS changed_at

          
            UNION ALL
          
        
          SELECT
              CONCAT(
                   
    TO_VARCHAR(
                        DATEADD(Day, -1 * 25, CURRENT_DATE),
                        'YYYYMMDD'
                   ),
                   '4'
             )::int                                                            AS order_no,

             UNIFORM(123456, 654321, RANDOM())                                  AS customer_id,

             
                'DELIVERED'                                                    AS status,
             
             DATEADD(Day, -1 * 25, CURRENT_DATE)                    AS created_at,
             current_timestamp                                                 AS changed_at

          
            UNION ALL
          
        
          SELECT
              CONCAT(
                   
    TO_VARCHAR(
                        DATEADD(Day, -1 * 25, CURRENT_DATE),
                        'YYYYMMDD'
                   ),
                   '5'
             )::int                                                            AS order_no,

             UNIFORM(123456, 654321, RANDOM())                                  AS customer_id,

             
                'DELIVERED'                                                    AS status,
             
             DATEADD(Day, -1 * 25, CURRENT_DATE)                    AS created_at,
             current_timestamp                                                 AS changed_at

          
            UNION ALL
          
        
          SELECT
              CONCAT(
                   
    TO_VARCHAR(
                        DATEADD(Day, -1 * 25, CURRENT_DATE),
                        'YYYYMMDD'
                   ),
                   '6'
             )::int                                                            AS order_no,

             UNIFORM(123456, 654321, RANDOM())                                  AS customer_id,

             
                'DELIVERED'                                                    AS status,
             
             DATEADD(Day, -1 * 25, CURRENT_DATE)                    AS created_at,
             current_timestamp                                                 AS changed_at

          
            UNION ALL
          
        
          SELECT
              CONCAT(
                   
    TO_VARCHAR(
                        DATEADD(Day, -1 * 25, CURRENT_DATE),
                        'YYYYMMDD'
                   ),
                   '7'
             )::int                                                            AS order_no,

             UNIFORM(123456, 654321, RANDOM())                                  AS customer_id,

             
                'DELIVERED'                                                    AS status,
             
             DATEADD(Day, -1 * 25, CURRENT_DATE)                    AS created_at,
             current_timestamp                                                 AS changed_at

          
            UNION ALL
          
        
          SELECT
              CONCAT(
                   
    TO_VARCHAR(
                        DATEADD(Day, -1 * 25, CURRENT_DATE),
                        'YYYYMMDD'
                   ),
                   '8'
             )::int                                                            AS order_no,

             UNIFORM(123456, 654321, RANDOM())                                  AS customer_id,

             
                'DELIVERED'                                                    AS status,
             
             DATEADD(Day, -1 * 25, CURRENT_DATE)                    AS created_at,
             current_timestamp                                                 AS changed_at

          
            UNION ALL
          
        
          SELECT
              CONCAT(
                   
    TO_VARCHAR(
                        DATEADD(Day, -1 * 25, CURRENT_DATE),
                        'YYYYMMDD'
                   ),
                   '9'
             )::int                                                            AS order_no,

             UNIFORM(123456, 654321, RANDOM())                                  AS customer_id,

             
                'DELIVERED'                                                    AS status,
             
             DATEADD(Day, -1 * 25, CURRENT_DATE)                    AS created_at,
             current_timestamp                                                 AS changed_at

          
        

        
            UNION ALL
        
    
        
          SELECT
              CONCAT(
                   
    TO_VARCHAR(
                        DATEADD(Day, -1 * 26, CURRENT_DATE),
                        'YYYYMMDD'
                   ),
                   '0'
             )::int                                                            AS order_no,

             UNIFORM(123456, 654321, RANDOM())                                  AS customer_id,

             
                'PENDING'                                                      AS status,
             
             DATEADD(Day, -1 * 26, CURRENT_DATE)                    AS created_at,
             current_timestamp                                                 AS changed_at

          
            UNION ALL
          
        
          SELECT
              CONCAT(
                   
    TO_VARCHAR(
                        DATEADD(Day, -1 * 26, CURRENT_DATE),
                        'YYYYMMDD'
                   ),
                   '1'
             )::int                                                            AS order_no,

             UNIFORM(123456, 654321, RANDOM())                                  AS customer_id,

             
                'DELIVERED'                                                    AS status,
             
             DATEADD(Day, -1 * 26, CURRENT_DATE)                    AS created_at,
             current_timestamp                                                 AS changed_at

          
            UNION ALL
          
        
          SELECT
              CONCAT(
                   
    TO_VARCHAR(
                        DATEADD(Day, -1 * 26, CURRENT_DATE),
                        'YYYYMMDD'
                   ),
                   '2'
             )::int                                                            AS order_no,

             UNIFORM(123456, 654321, RANDOM())                                  AS customer_id,

             
                'DELIVERED'                                                    AS status,
             
             DATEADD(Day, -1 * 26, CURRENT_DATE)                    AS created_at,
             current_timestamp                                                 AS changed_at

          
            UNION ALL
          
        
          SELECT
              CONCAT(
                   
    TO_VARCHAR(
                        DATEADD(Day, -1 * 26, CURRENT_DATE),
                        'YYYYMMDD'
                   ),
                   '3'
             )::int                                                            AS order_no,

             UNIFORM(123456, 654321, RANDOM())                                  AS customer_id,

             
                'DELIVERED'                                                    AS status,
             
             DATEADD(Day, -1 * 26, CURRENT_DATE)                    AS created_at,
             current_timestamp                                                 AS changed_at

          
            UNION ALL
          
        
          SELECT
              CONCAT(
                   
    TO_VARCHAR(
                        DATEADD(Day, -1 * 26, CURRENT_DATE),
                        'YYYYMMDD'
                   ),
                   '4'
             )::int                                                            AS order_no,

             UNIFORM(123456, 654321, RANDOM())                                  AS customer_id,

             
                'DELIVERED'                                                    AS status,
             
             DATEADD(Day, -1 * 26, CURRENT_DATE)                    AS created_at,
             current_timestamp                                                 AS changed_at

          
            UNION ALL
          
        
          SELECT
              CONCAT(
                   
    TO_VARCHAR(
                        DATEADD(Day, -1 * 26, CURRENT_DATE),
                        'YYYYMMDD'
                   ),
                   '5'
             )::int                                                            AS order_no,

             UNIFORM(123456, 654321, RANDOM())                                  AS customer_id,

             
                'DELIVERED'                                                    AS status,
             
             DATEADD(Day, -1 * 26, CURRENT_DATE)                    AS created_at,
             current_timestamp                                                 AS changed_at

          
            UNION ALL
          
        
          SELECT
              CONCAT(
                   
    TO_VARCHAR(
                        DATEADD(Day, -1 * 26, CURRENT_DATE),
                        'YYYYMMDD'
                   ),
                   '6'
             )::int                                                            AS order_no,

             UNIFORM(123456, 654321, RANDOM())                                  AS customer_id,

             
                'DELIVERED'                                                    AS status,
             
             DATEADD(Day, -1 * 26, CURRENT_DATE)                    AS created_at,
             current_timestamp                                                 AS changed_at

          
            UNION ALL
          
        
          SELECT
              CONCAT(
                   
    TO_VARCHAR(
                        DATEADD(Day, -1 * 26, CURRENT_DATE),
                        'YYYYMMDD'
                   ),
                   '7'
             )::int                                                            AS order_no,

             UNIFORM(123456, 654321, RANDOM())                                  AS customer_id,

             
                'DELIVERED'                                                    AS status,
             
             DATEADD(Day, -1 * 26, CURRENT_DATE)                    AS created_at,
             current_timestamp                                                 AS changed_at

          
            UNION ALL
          
        
          SELECT
              CONCAT(
                   
    TO_VARCHAR(
                        DATEADD(Day, -1 * 26, CURRENT_DATE),
                        'YYYYMMDD'
                   ),
                   '8'
             )::int                                                            AS order_no,

             UNIFORM(123456, 654321, RANDOM())                                  AS customer_id,

             
                'DELIVERED'                                                    AS status,
             
             DATEADD(Day, -1 * 26, CURRENT_DATE)                    AS created_at,
             current_timestamp                                                 AS changed_at

          
            UNION ALL
          
        
          SELECT
              CONCAT(
                   
    TO_VARCHAR(
                        DATEADD(Day, -1 * 26, CURRENT_DATE),
                        'YYYYMMDD'
                   ),
                   '9'
             )::int                                                            AS order_no,

             UNIFORM(123456, 654321, RANDOM())                                  AS customer_id,

             
                'DELIVERED'                                                    AS status,
             
             DATEADD(Day, -1 * 26, CURRENT_DATE)                    AS created_at,
             current_timestamp                                                 AS changed_at

          
        

        
            UNION ALL
        
    
        
          SELECT
              CONCAT(
                   
    TO_VARCHAR(
                        DATEADD(Day, -1 * 27, CURRENT_DATE),
                        'YYYYMMDD'
                   ),
                   '0'
             )::int                                                            AS order_no,

             UNIFORM(123456, 654321, RANDOM())                                  AS customer_id,

             
                'PENDING'                                                      AS status,
             
             DATEADD(Day, -1 * 27, CURRENT_DATE)                    AS created_at,
             current_timestamp                                                 AS changed_at

          
            UNION ALL
          
        
          SELECT
              CONCAT(
                   
    TO_VARCHAR(
                        DATEADD(Day, -1 * 27, CURRENT_DATE),
                        'YYYYMMDD'
                   ),
                   '1'
             )::int                                                            AS order_no,

             UNIFORM(123456, 654321, RANDOM())                                  AS customer_id,

             
                'DELIVERED'                                                    AS status,
             
             DATEADD(Day, -1 * 27, CURRENT_DATE)                    AS created_at,
             current_timestamp                                                 AS changed_at

          
            UNION ALL
          
        
          SELECT
              CONCAT(
                   
    TO_VARCHAR(
                        DATEADD(Day, -1 * 27, CURRENT_DATE),
                        'YYYYMMDD'
                   ),
                   '2'
             )::int                                                            AS order_no,

             UNIFORM(123456, 654321, RANDOM())                                  AS customer_id,

             
                'DELIVERED'                                                    AS status,
             
             DATEADD(Day, -1 * 27, CURRENT_DATE)                    AS created_at,
             current_timestamp                                                 AS changed_at

          
            UNION ALL
          
        
          SELECT
              CONCAT(
                   
    TO_VARCHAR(
                        DATEADD(Day, -1 * 27, CURRENT_DATE),
                        'YYYYMMDD'
                   ),
                   '3'
             )::int                                                            AS order_no,

             UNIFORM(123456, 654321, RANDOM())                                  AS customer_id,

             
                'DELIVERED'                                                    AS status,
             
             DATEADD(Day, -1 * 27, CURRENT_DATE)                    AS created_at,
             current_timestamp                                                 AS changed_at

          
            UNION ALL
          
        
          SELECT
              CONCAT(
                   
    TO_VARCHAR(
                        DATEADD(Day, -1 * 27, CURRENT_DATE),
                        'YYYYMMDD'
                   ),
                   '4'
             )::int                                                            AS order_no,

             UNIFORM(123456, 654321, RANDOM())                                  AS customer_id,

             
                'DELIVERED'                                                    AS status,
             
             DATEADD(Day, -1 * 27, CURRENT_DATE)                    AS created_at,
             current_timestamp                                                 AS changed_at

          
            UNION ALL
          
        
          SELECT
              CONCAT(
                   
    TO_VARCHAR(
                        DATEADD(Day, -1 * 27, CURRENT_DATE),
                        'YYYYMMDD'
                   ),
                   '5'
             )::int                                                            AS order_no,

             UNIFORM(123456, 654321, RANDOM())                                  AS customer_id,

             
                'DELIVERED'                                                    AS status,
             
             DATEADD(Day, -1 * 27, CURRENT_DATE)                    AS created_at,
             current_timestamp                                                 AS changed_at

          
            UNION ALL
          
        
          SELECT
              CONCAT(
                   
    TO_VARCHAR(
                        DATEADD(Day, -1 * 27, CURRENT_DATE),
                        'YYYYMMDD'
                   ),
                   '6'
             )::int                                                            AS order_no,

             UNIFORM(123456, 654321, RANDOM())                                  AS customer_id,

             
                'DELIVERED'                                                    AS status,
             
             DATEADD(Day, -1 * 27, CURRENT_DATE)                    AS created_at,
             current_timestamp                                                 AS changed_at

          
            UNION ALL
          
        
          SELECT
              CONCAT(
                   
    TO_VARCHAR(
                        DATEADD(Day, -1 * 27, CURRENT_DATE),
                        'YYYYMMDD'
                   ),
                   '7'
             )::int                                                            AS order_no,

             UNIFORM(123456, 654321, RANDOM())                                  AS customer_id,

             
                'DELIVERED'                                                    AS status,
             
             DATEADD(Day, -1 * 27, CURRENT_DATE)                    AS created_at,
             current_timestamp                                                 AS changed_at

          
            UNION ALL
          
        
          SELECT
              CONCAT(
                   
    TO_VARCHAR(
                        DATEADD(Day, -1 * 27, CURRENT_DATE),
                        'YYYYMMDD'
                   ),
                   '8'
             )::int                                                            AS order_no,

             UNIFORM(123456, 654321, RANDOM())                                  AS customer_id,

             
                'DELIVERED'                                                    AS status,
             
             DATEADD(Day, -1 * 27, CURRENT_DATE)                    AS created_at,
             current_timestamp                                                 AS changed_at

          
            UNION ALL
          
        
          SELECT
              CONCAT(
                   
    TO_VARCHAR(
                        DATEADD(Day, -1 * 27, CURRENT_DATE),
                        'YYYYMMDD'
                   ),
                   '9'
             )::int                                                            AS order_no,

             UNIFORM(123456, 654321, RANDOM())                                  AS customer_id,

             
                'DELIVERED'                                                    AS status,
             
             DATEADD(Day, -1 * 27, CURRENT_DATE)                    AS created_at,
             current_timestamp                                                 AS changed_at

          
        

        
            UNION ALL
        
    
        
          SELECT
              CONCAT(
                   
    TO_VARCHAR(
                        DATEADD(Day, -1 * 28, CURRENT_DATE),
                        'YYYYMMDD'
                   ),
                   '0'
             )::int                                                            AS order_no,

             UNIFORM(123456, 654321, RANDOM())                                  AS customer_id,

             
                'PENDING'                                                      AS status,
             
             DATEADD(Day, -1 * 28, CURRENT_DATE)                    AS created_at,
             current_timestamp                                                 AS changed_at

          
            UNION ALL
          
        
          SELECT
              CONCAT(
                   
    TO_VARCHAR(
                        DATEADD(Day, -1 * 28, CURRENT_DATE),
                        'YYYYMMDD'
                   ),
                   '1'
             )::int                                                            AS order_no,

             UNIFORM(123456, 654321, RANDOM())                                  AS customer_id,

             
                'DELIVERED'                                                    AS status,
             
             DATEADD(Day, -1 * 28, CURRENT_DATE)                    AS created_at,
             current_timestamp                                                 AS changed_at

          
            UNION ALL
          
        
          SELECT
              CONCAT(
                   
    TO_VARCHAR(
                        DATEADD(Day, -1 * 28, CURRENT_DATE),
                        'YYYYMMDD'
                   ),
                   '2'
             )::int                                                            AS order_no,

             UNIFORM(123456, 654321, RANDOM())                                  AS customer_id,

             
                'DELIVERED'                                                    AS status,
             
             DATEADD(Day, -1 * 28, CURRENT_DATE)                    AS created_at,
             current_timestamp                                                 AS changed_at

          
            UNION ALL
          
        
          SELECT
              CONCAT(
                   
    TO_VARCHAR(
                        DATEADD(Day, -1 * 28, CURRENT_DATE),
                        'YYYYMMDD'
                   ),
                   '3'
             )::int                                                            AS order_no,

             UNIFORM(123456, 654321, RANDOM())                                  AS customer_id,

             
                'DELIVERED'                                                    AS status,
             
             DATEADD(Day, -1 * 28, CURRENT_DATE)                    AS created_at,
             current_timestamp                                                 AS changed_at

          
            UNION ALL
          
        
          SELECT
              CONCAT(
                   
    TO_VARCHAR(
                        DATEADD(Day, -1 * 28, CURRENT_DATE),
                        'YYYYMMDD'
                   ),
                   '4'
             )::int                                                            AS order_no,

             UNIFORM(123456, 654321, RANDOM())                                  AS customer_id,

             
                'DELIVERED'                                                    AS status,
             
             DATEADD(Day, -1 * 28, CURRENT_DATE)                    AS created_at,
             current_timestamp                                                 AS changed_at

          
            UNION ALL
          
        
          SELECT
              CONCAT(
                   
    TO_VARCHAR(
                        DATEADD(Day, -1 * 28, CURRENT_DATE),
                        'YYYYMMDD'
                   ),
                   '5'
             )::int                                                            AS order_no,

             UNIFORM(123456, 654321, RANDOM())                                  AS customer_id,

             
                'DELIVERED'                                                    AS status,
             
             DATEADD(Day, -1 * 28, CURRENT_DATE)                    AS created_at,
             current_timestamp                                                 AS changed_at

          
            UNION ALL
          
        
          SELECT
              CONCAT(
                   
    TO_VARCHAR(
                        DATEADD(Day, -1 * 28, CURRENT_DATE),
                        'YYYYMMDD'
                   ),
                   '6'
             )::int                                                            AS order_no,

             UNIFORM(123456, 654321, RANDOM())                                  AS customer_id,

             
                'DELIVERED'                                                    AS status,
             
             DATEADD(Day, -1 * 28, CURRENT_DATE)                    AS created_at,
             current_timestamp                                                 AS changed_at

          
            UNION ALL
          
        
          SELECT
              CONCAT(
                   
    TO_VARCHAR(
                        DATEADD(Day, -1 * 28, CURRENT_DATE),
                        'YYYYMMDD'
                   ),
                   '7'
             )::int                                                            AS order_no,

             UNIFORM(123456, 654321, RANDOM())                                  AS customer_id,

             
                'DELIVERED'                                                    AS status,
             
             DATEADD(Day, -1 * 28, CURRENT_DATE)                    AS created_at,
             current_timestamp                                                 AS changed_at

          
            UNION ALL
          
        
          SELECT
              CONCAT(
                   
    TO_VARCHAR(
                        DATEADD(Day, -1 * 28, CURRENT_DATE),
                        'YYYYMMDD'
                   ),
                   '8'
             )::int                                                            AS order_no,

             UNIFORM(123456, 654321, RANDOM())                                  AS customer_id,

             
                'DELIVERED'                                                    AS status,
             
             DATEADD(Day, -1 * 28, CURRENT_DATE)                    AS created_at,
             current_timestamp                                                 AS changed_at

          
            UNION ALL
          
        
          SELECT
              CONCAT(
                   
    TO_VARCHAR(
                        DATEADD(Day, -1 * 28, CURRENT_DATE),
                        'YYYYMMDD'
                   ),
                   '9'
             )::int                                                            AS order_no,

             UNIFORM(123456, 654321, RANDOM())                                  AS customer_id,

             
                'DELIVERED'                                                    AS status,
             
             DATEADD(Day, -1 * 28, CURRENT_DATE)                    AS created_at,
             current_timestamp                                                 AS changed_at

          
        

        
            UNION ALL
        
    
        
          SELECT
              CONCAT(
                   
    TO_VARCHAR(
                        DATEADD(Day, -1 * 29, CURRENT_DATE),
                        'YYYYMMDD'
                   ),
                   '0'
             )::int                                                            AS order_no,

             UNIFORM(123456, 654321, RANDOM())                                  AS customer_id,

             
                'PENDING'                                                      AS status,
             
             DATEADD(Day, -1 * 29, CURRENT_DATE)                    AS created_at,
             current_timestamp                                                 AS changed_at

          
            UNION ALL
          
        
          SELECT
              CONCAT(
                   
    TO_VARCHAR(
                        DATEADD(Day, -1 * 29, CURRENT_DATE),
                        'YYYYMMDD'
                   ),
                   '1'
             )::int                                                            AS order_no,

             UNIFORM(123456, 654321, RANDOM())                                  AS customer_id,

             
                'DELIVERED'                                                    AS status,
             
             DATEADD(Day, -1 * 29, CURRENT_DATE)                    AS created_at,
             current_timestamp                                                 AS changed_at

          
            UNION ALL
          
        
          SELECT
              CONCAT(
                   
    TO_VARCHAR(
                        DATEADD(Day, -1 * 29, CURRENT_DATE),
                        'YYYYMMDD'
                   ),
                   '2'
             )::int                                                            AS order_no,

             UNIFORM(123456, 654321, RANDOM())                                  AS customer_id,

             
                'DELIVERED'                                                    AS status,
             
             DATEADD(Day, -1 * 29, CURRENT_DATE)                    AS created_at,
             current_timestamp                                                 AS changed_at

          
            UNION ALL
          
        
          SELECT
              CONCAT(
                   
    TO_VARCHAR(
                        DATEADD(Day, -1 * 29, CURRENT_DATE),
                        'YYYYMMDD'
                   ),
                   '3'
             )::int                                                            AS order_no,

             UNIFORM(123456, 654321, RANDOM())                                  AS customer_id,

             
                'DELIVERED'                                                    AS status,
             
             DATEADD(Day, -1 * 29, CURRENT_DATE)                    AS created_at,
             current_timestamp                                                 AS changed_at

          
            UNION ALL
          
        
          SELECT
              CONCAT(
                   
    TO_VARCHAR(
                        DATEADD(Day, -1 * 29, CURRENT_DATE),
                        'YYYYMMDD'
                   ),
                   '4'
             )::int                                                            AS order_no,

             UNIFORM(123456, 654321, RANDOM())                                  AS customer_id,

             
                'DELIVERED'                                                    AS status,
             
             DATEADD(Day, -1 * 29, CURRENT_DATE)                    AS created_at,
             current_timestamp                                                 AS changed_at

          
            UNION ALL
          
        
          SELECT
              CONCAT(
                   
    TO_VARCHAR(
                        DATEADD(Day, -1 * 29, CURRENT_DATE),
                        'YYYYMMDD'
                   ),
                   '5'
             )::int                                                            AS order_no,

             UNIFORM(123456, 654321, RANDOM())                                  AS customer_id,

             
                'DELIVERED'                                                    AS status,
             
             DATEADD(Day, -1 * 29, CURRENT_DATE)                    AS created_at,
             current_timestamp                                                 AS changed_at

          
            UNION ALL
          
        
          SELECT
              CONCAT(
                   
    TO_VARCHAR(
                        DATEADD(Day, -1 * 29, CURRENT_DATE),
                        'YYYYMMDD'
                   ),
                   '6'
             )::int                                                            AS order_no,

             UNIFORM(123456, 654321, RANDOM())                                  AS customer_id,

             
                'DELIVERED'                                                    AS status,
             
             DATEADD(Day, -1 * 29, CURRENT_DATE)                    AS created_at,
             current_timestamp                                                 AS changed_at

          
            UNION ALL
          
        
          SELECT
              CONCAT(
                   
    TO_VARCHAR(
                        DATEADD(Day, -1 * 29, CURRENT_DATE),
                        'YYYYMMDD'
                   ),
                   '7'
             )::int                                                            AS order_no,

             UNIFORM(123456, 654321, RANDOM())                                  AS customer_id,

             
                'DELIVERED'                                                    AS status,
             
             DATEADD(Day, -1 * 29, CURRENT_DATE)                    AS created_at,
             current_timestamp                                                 AS changed_at

          
            UNION ALL
          
        
          SELECT
              CONCAT(
                   
    TO_VARCHAR(
                        DATEADD(Day, -1 * 29, CURRENT_DATE),
                        'YYYYMMDD'
                   ),
                   '8'
             )::int                                                            AS order_no,

             UNIFORM(123456, 654321, RANDOM())                                  AS customer_id,

             
                'DELIVERED'                                                    AS status,
             
             DATEADD(Day, -1 * 29, CURRENT_DATE)                    AS created_at,
             current_timestamp                                                 AS changed_at

          
            UNION ALL
          
        
          SELECT
              CONCAT(
                   
    TO_VARCHAR(
                        DATEADD(Day, -1 * 29, CURRENT_DATE),
                        'YYYYMMDD'
                   ),
                   '9'
             )::int                                                            AS order_no,

             UNIFORM(123456, 654321, RANDOM())                                  AS customer_id,

             
                'DELIVERED'                                                    AS status,
             
             DATEADD(Day, -1 * 29, CURRENT_DATE)                    AS created_at,
             current_timestamp                                                 AS changed_at

          
        

        
    
)

SELECT *
FROM generated_orders

