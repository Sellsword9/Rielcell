use sqlx::mysql::MySqlPool;
/*use sqlx::Pool;
use sqlx::MySql;
*/
pub async fn pool() -> MySqlPool {
    // Define your database URL
    let database_url = "mysql://root:pass@localhost/rust_test_data";

    // Create a database connection pool
    let pool = MySqlPool::connect(database_url)
        .await
        .expect("Failed to create database pool");

    pool
}
