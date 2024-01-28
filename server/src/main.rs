use actix_http::HttpMessage;
use actix_web::cookie::Cookie;
use actix_web::dev::Service;
use actix_web::web::{Data, Form};
use actix_web::{get, post, App, HttpRequest, HttpResponse, HttpServer}; // You may want to use web too, but template doesn't need it
use actix_files::Files;
use sqlx::mysql::MySqlPool;

use sqlx::Row;
/*use sqlx::MySqlConnection;
use sqlx_mysql;
use sqlx_mysql::MySqlRow; 
use sqlx::Connection;
use sqlx::Executor;
use actix_web::FromRequest;*/
mod db;
#[actix_web::main]
async fn main() -> std::io::Result<()> {
    let pool = db::pool().await;
    HttpServer::new(move|| { 
        App::new()
            .app_data(Data::new(pool.clone()))
            .service(logout)
            .service(login)
            .service(Files::new("/", "src").index_file("index.html"))
            .wrap_fn(|req, srv| {
                let user_id: Option<i32> = req
                    .cookie("user_id")
                    .and_then(|cookie| cookie.value().parse().ok());

                let is_authenticated = user_id.is_some();
                req.extensions_mut().insert(is_authenticated);
                srv.call(req)
            })
    })
    .bind("127.0.0.1:8080")? 
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
#[derive(serde::Deserialize)]
struct LoginParams {
    username: String,
    password: String,
}
#[post("/login")]
async fn login(form: Form<LoginParams>, db: Data<MySqlPool>) -> HttpResponse {
    let username = &form.username;
    let password = &form.password;

    // Query the database to check if the username and password match
    let query_result = 
        sqlx::query("SELECT user_id FROM users WHERE username = ? AND password = ?")
            .bind(username)
            .bind(password)
        .fetch_optional(db.get_ref())
        .await;

    match query_result {
        Ok(Some(row)) => {
            // Authentication successful
            let user_id: i32 = row.get("user_id");

            HttpResponse::Ok()
                .cookie(Cookie::build("user_id", user_id.to_string()).path("/").finish())
                .finish()
        }
        _ => {
            // Authentication failed
            HttpResponse::Unauthorized().finish()
        }
    }
}