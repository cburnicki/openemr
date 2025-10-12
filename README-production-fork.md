# OpenEMR Enhanced Fork

This is our enhanced fork of the [OpenEMR](https://github.com/openemr/openemr) electronic health records system. We maintain this fork to accelerate development and deployment of critical fixes and features while continuing to contribute back to the upstream project.

## Why This Fork Exists

The upstream OpenEMR project has a slow integration process for pull requests. While this ensures stability, it creates delays when we need urgent fixes or features for our production environment. This fork allows us to:

-   **Deploy fixes immediately** without waiting for upstream approval
-   **Test new features** in our environment before they're accepted upstream
-   **Maintain stability** by controlling what gets deployed to production
-   **Continue contributing** to the open source project with clean PRs

## What's Different

This fork includes:

-   Bug fixes that haven't been merged upstream yet
-   Performance improvements specific to our use case
-   Custom features that may eventually be contributed back
-   Experimental changes being tested before upstream submission

## Installation

Install directly from this repository instead of the official OpenEMR:

```bash
git clone https://github.com/[your-username]/openemr.git
cd openemr
git checkout production
# Follow standard OpenEMR installation process
```

## Contributing

We welcome contributions! Please follow our branching strategy below to ensure clean integration.

## Branching Strategy

### Main Branches

-   **`upstream-sync`** - Clean mirror of official OpenEMR repository
-   **`production`** - Our deployment branch (what we install from)

### The Process

We maintain our own fast-moving production environment while keeping clean contribution paths to the upstream OpenEMR project.

### Initial Setup

```bash
# Add upstream remote (one time setup)
git remote add upstream https://github.com/openemr/openemr.git

# Set up upstream-sync branch
git checkout -b upstream-sync
git pull upstream master
git push -u origin upstream-sync
```

### Creating a New Feature/Fix

```bash
# 1. Start from clean upstream
git checkout upstream-sync
git pull upstream master
git push origin upstream-sync

# 2. Create feature branch
git checkout -b feature/your-feature-name

# 3. Make your changes and commit
git add .
git commit -m "Fix: your changes"

# 4. Push feature branch
git push -u origin feature/your-feature-name
```

### Getting Feature into Production

```bash
# When you're confident the feature is stable
git checkout production
git merge feature/your-feature-name
git push origin production

# Deploy manually from production branch
```

### Creating PR to Upstream (Optional)

Only create PRs for features that would benefit the broader OpenEMR community:

1. Go to GitHub and create PR from your `feature/your-feature-name` branch
2. Target the upstream repository's main branch
3. **Never create PRs from your production branch**
4. Not every feature needs to be contributed - keep organization-specific customizations in your fork

### Staying Synced with Upstream

```bash
# Regular sync (do this weekly)
git checkout upstream-sync
git pull upstream master
git push origin upstream-sync

# Then merge important upstream changes to production as needed
git checkout production
git merge upstream-sync
```

### Important Notes

-   **Never use GitHub's "Sync fork" button** - it creates messy merge commits
-   Always create feature branches from `upstream-sync` for clean PR history
-   Deploy manually from `production` branch when you're confident changes are stable
-   Merge features directly to `production` when ready

## Build Production Image From odovita/openemr

The steps below live in the `openemr-devops` tooling repo but build the application from this fork.

1. Clone the tooling repo and enter it:

    ```bash
    git clone https://github.com/openemr/openemr-devops.git
    cd openemr-devops
    ```

2. Copy the latest production image definition so we can customize it without touching upstream files:

    ```bash
    cp -a docker/openemr/7.0.4 docker/openemr/7.0.4-odovita
    ```

3. Edit `docker/openemr/7.0.4-odovita/Dockerfile` and change the `git clone` line (around line 90) to pull our fork’s `production` branch:

    ```Dockerfile
    RUN apk add --no-cache build-base \
        && git clone --branch production https://github.com/odovita/openemr.git --depth 1 \
        && rm -rf openemr/.git \
        && cd openemr \
        ...
    ```

    Keep the rest of the build block unchanged so Composer/NPM steps remain identical to the official image.

4. Build the Docker image through the bundled `docker compose` stack:

    ```bash
    cd docker/openemr
    export DOCKER_CONTEXT_PATH=7.0.4-odovita
    COMPOSE_PROFILES=prod docker compose build
    ```

5. (Optional) Smoke-test the container with MariaDB:

    ```bash
    COMPOSE_PROFILES=prod docker compose up -d --wait
    # browse http://localhost:8080 or inspect logs
    docker compose logs openemr
    docker compose down --volumes
    ```

6. Push a multi-arch image to your registry when you are ready:

    ```bash
    docker buildx build \
      --platform linux/amd64,linux/arm64 \
      -t ghcr.io/odovita/openemr:production \
      --push docker/openemr/7.0.4-odovita
    ```

### Keeping The Fork Updated

-   Regularly sync with upstream:
    ```bash
    git remote add upstream https://github.com/openemr/openemr.git
    git fetch upstream
    git rebase upstream/master   # or merge, depending on your workflow
    ```
-   After merging upstream changes into `odovita/openemr`, rebuild following the steps above to produce a fresh production image.

## License

Same as upstream OpenEMR - GNU General Public License v3.0
