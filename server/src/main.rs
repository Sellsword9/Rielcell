use std::collections::HashMap;
use std::fs;
use std::hash::Hasher;

use actix_web::*;
use actix_web::cookie::Cookie;
use actix_web::web::{Data, Form};
use actix_files::*;
use mysql::prelude::Queryable;
use mysql::{params, Pool, PooledConn};
use serde::{Serialize, Deserialize};
mod db;
#[derive(Serialize, Deserialize, Debug, Clone)]
struct User {
    id: i32,
    username: String,
    password: String,
}

#[actix_web::main]
async fn main() -> std::io::Result<()> {
    let pool = db::pool();
    if pool.is_none() {
        panic!("Failed to create pool");
    }
    let addrs = "localhost:4000";
    HttpServer::new(move || {
        App::new()
            .app_data(Data::new(pool.clone().unwrap()))
            .service(login)
            .service(logout)
            .service(index)
    }).bind(addrs)?
        .run()
        .await
}


#[post("/login")]
async fn login(req: HttpRequest) -> HttpResponse {
    let form = Form::<HashMap<String, String>>::extract(&req).await.unwrap();
    let username = form.get("username").unwrap();
    let password = form.get("password").unwrap();
    let pool = req.app_data::<Pool>().unwrap();
    let pool: Pool = pool.clone();
    let logged = login_user(pool, username, password).await;
    return HttpResponse::Ok().body("Testing");
    /*if logged.is_some() {
        HttpResponse::Ok()
            .cookie(Cookie::build("user_id", ).path("/").finish())
            .finish()
        HttpResponse::Ok().finish()
    } else {
        HttpResponse::Unauthorized().finish()
    }*/
}
#[get("/")]
async fn index() -> Result<NamedFile> {
    Ok(NamedFile::open("view/index.html")?)
}

async fn login_user(pool: Pool, username: &str, password: &str) -> Option<User> {
    let mut conn: PooledConn = pool.get_conn().unwrap();
    return None;
}
#[get("/logout")]
async fn logout(req: HttpRequest) -> HttpResponse {
    if let Some(_) = req.cookie("user_id") {
        HttpResponse::Ok()
            .cookie(Cookie::build("user_id", "").path("/").finish())
            .finish()
    } else {
        HttpResponse::Unauthorized().finish()
    }
}