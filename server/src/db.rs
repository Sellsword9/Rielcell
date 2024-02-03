use mysql::*;
use mysql::prelude::*;
pub fn pool() -> Option<Pool> {
    let url = std::env::var("DATABASE_URL").ok()?;
    let opts = Opts::from_url(&url).ok()?;
    let pool = Pool::new(opts).ok()?;
    Some(pool)
}