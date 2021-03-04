# kong-oauth2-demo
This is a simple demo to work with Kong oauth2 plugin showing 4 different grant flows

I've also made a docker image at `fomm/kong-oauth2-demo`

You can run it with `docker run -d -p 8080:80 fomm/kong-oauth2-demo` then you can open your browser localhost:8080 to access the tool.

This tool by default skip ssl validation, there is a toggle switch to turn off dev-mode (skip tls, default is on).
