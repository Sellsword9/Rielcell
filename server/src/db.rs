use mysql::*;
use mysql::prelude::*;
use dotenv::dotenv;
pub fn pool() -> Option<Pool> {
    dotenv().ok();
    let url = dotenv::var("DATABASE_URL").ok()?;
    let opts = Opts::from_url(&url).ok()?;
    let pool = Pool::new(opts).ok();
    pool
}