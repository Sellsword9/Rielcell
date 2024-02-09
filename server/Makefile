BIND = 127.0.0.1:4000
run:
	cargo build
	xdg-open http://${BIND} & cargo run
NAME = "server"
cleanbuild:
	cargo build --release
	strip target/release/$(NAME)
	cp target/release/$(NAME) ./$(NAME)
	cargo clean > /dev/null 2>&1
	@echo "Build complete"