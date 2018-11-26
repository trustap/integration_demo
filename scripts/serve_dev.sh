# `$0` runs the Docker image for this project.

docker run \
    --rm \
    -ti \
    -p 8080:80 \
    -v $(pwd):/var/www/html \
    trustap-integration-demo
