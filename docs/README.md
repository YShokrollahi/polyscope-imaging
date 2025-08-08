# Polyscope Documentation

## Local testing
1. Build: 
`docker run --rm -v ${PWD}:/docs squidfunk/mkdocs-material build`
2. Serve: 
`docker run --rm -p 8000:8000 -v ${PWD}:/docs squidfunk/mkdocs-material serve -a 0.0.0.0:8000`.
3. Access: 
`http://localhost:8000/`

## Deployment
GitHub Workflow is setup at /.github/workflows/mkdocs.yaml so that changes to `mkdocs.yml` and `/docs` will trigger automatic documentation page rebuild.
