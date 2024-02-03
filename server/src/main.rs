use mysql::*;
use actix_web::*;
use actix_web::cookie::Cookie;
use actix_web::web::{Data, Form};
use actix_files::*;
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
    let addrs = "localhost:8000";
    HttpServer::new(move || {
        App::new()
            .app_data(pool.clone())
            .service(login)
            .service(logout)
    }).bind(addrs)?
        .run()
        .await
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
#[post("/login")]
async fn login(form: Form<User>, db: Data<User>) -> HttpResponse {
    HttpResponse::Unauthorized().finish()
}