# Notes
## How to create and push a new docker image
1. Build the docker file via
```
docker build -t "<dockerRegistryUrl>:<tag>" .
```
2. Push the image to the registry
```
docker push -t "<dockerRegistryUrl>:<tag>" .
```

## Restart the docker compose container
1. Update the tag in the docker-compose.yml
2. Restart the compose container
```
docker-compose up -d
```