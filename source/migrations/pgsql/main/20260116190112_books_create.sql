-- @up
CREATE TABLE IF NOT EXISTS public.books (
    id serial NOT NULL,
    title varchar NOT NULL,
    created_at timestamp(0) DEFAULT CURRENT_TIMESTAMP NOT NULL,
    updated_at timestamp(0) DEFAULT CURRENT_TIMESTAMP NOT NULL,
    CONSTRAINT books_pkey PRIMARY KEY (id)
);

-- @down
DROP TABLE IF EXISTS public.books;
