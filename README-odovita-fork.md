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

-   **`upstream-sync-<version>`** - Clean mirror of official OpenEMR version branch (e.g., `upstream-sync-7_0_3`)
-   **`production`** - Our deployment branch (what we install from)
-   **`release-<version>-odovita`** - Our versioned release branches (e.g., `release-7.0.3-odovita`)

### The Process

We maintain our own fast-moving production environment while syncing with stable upstream release branches (NOT master, which contains unreleased code).

### Initial Setup

```bash
# Add upstream remote (one time setup)
git remote add upstream https://github.com/openemr/openemr.git

# Fetch all upstream branches
git fetch upstream
```

### Syncing with a New Upstream Release

**IMPORTANT**: Never sync with upstream's `master` branch - it contains unreleased code. Always sync with official release branches.

```bash
# 1. Determine the latest stable release version (e.g., 7.0.3)
# Check https://github.com/openemr/openemr/releases for the latest version

# 2. Check the openemr-devops repo to find the branch name format
# Look in https://github.com/openemr/openemr-devops/tree/master/docker/openemr/7.0.3
# Inspect the Dockerfile to see which branch it uses (usually rel-703 or 7_0_3 format)

# 3. Create a versioned upstream sync branch
git fetch upstream
git checkout -b upstream-sync-7_0_3 upstream/rel-703  # Use actual branch name from step 2
git push -u origin upstream-sync-7_0_3

# 4. Merge into production
git checkout production
git merge upstream-sync-7_0_3
git push origin production

# 5. Create your own versioned release branch
git checkout -b release-7.0.3-odovita
git push -u origin release-7.0.3-odovita
```

### Creating a New Feature/Fix

```bash
# 1. Start from the appropriate upstream sync branch
git checkout upstream-sync-7_0_3  # Use your current version

# 2. Create feature branch
git checkout -b feature/your-feature-name

# 3. Make your changes and commit
git add .
git commit -m "Fix: your changes"

# 4. Push feature branch
git push -u origin feature/your-feature-name

# 5. Merge into production when ready
git checkout production
git merge feature/your-feature-name
git push origin production
```

### Creating PR to Upstream (Optional)

Only create PRs for features that would benefit the broader OpenEMR community:

1. Go to GitHub and create PR from your `feature/your-feature-name` branch
2. Target the upstream repository's appropriate release branch
3. **Never create PRs from your production branch**
4. Not every feature needs to be contributed - keep organization-specific customizations in your fork

### Important Notes

-   **Never sync with upstream's `master` branch** - it contains unreleased, unstable code
-   **Always sync with versioned release branches** (e.g., `rel-703`, `7_0_3`) found in openemr-devops Dockerfiles
-   **Never use GitHub's "Sync fork" button** - it creates messy merge commits
-   Always create feature branches from versioned `upstream-sync-<version>` branches for clean PR history
-   Deploy manually from `production` branch when you're confident changes are stable

## Build Production Image From odovita/openemr

The steps below live in the `openemr-devops` tooling repo but build the application from this fork.

1. Clone the tooling repo and enter it:

    ```bash
    git clone https://github.com/openemr/openemr-devops.git
    cd openemr-devops
    ```

2. Find the correct version directory (e.g., 7.0.3) and copy it:

    ```bash
    cp -a docker/openemr/7.0.3 docker/openemr/7.0.3-odovita
    ```

3. Edit `docker/openemr/7.0.3-odovita/Dockerfile` and change the `git clone` line to pull from our fork's versioned release branch:

    ```Dockerfile
    RUN apk add --no-cache build-base \
        && git clone --branch release-7.0.3-odovita https://github.com/odovita/openemr.git --depth 1 \
        && rm -rf openemr/.git \
        && cd openemr \
        ...
    ```

    Keep the rest of the build block unchanged so Composer/NPM steps remain identical to the official image.

4. Build the Docker image through the bundled `docker compose` stack:

    ```bash
    cd docker/openemr
    export DOCKER_CONTEXT_PATH=7.0.3-odovita
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
      -t ghcr.io/odovita/openemr:7.0.3-odovita \
      --push docker/openemr/7.0.3-odovita
    ```

### Keeping The Fork Updated

When a new OpenEMR version is released, follow the "Syncing with a New Upstream Release" steps above, then rebuild your Docker image with the new version number.

## License

Same as upstream OpenEMR - GNU General Public License v3.0

---

## Technical Notes for Claude

This section contains technical context for AI assistants working on this codebase.

### Custom Commits Overview

When rebasing/cherry-picking our custom commits onto a new upstream version, be aware that some fixes may have been incorporated upstream:

#### Drug Dispensing Fix (PR #8598)
- **Original issue**: Drug dispensing failed silently when no encounter was selected
- **Our fix**: Added encounter validation in `sellDrug()` function with user-friendly error messages
- **Status as of rel-704**: **MERGED UPSTREAM** - The fix was incorporated into `src/Services/DrugSalesService.php` (see lines 244-258) with credit to this fork's PR
- **Action**: Skip this commit when rebasing onto rel-704 or later - the fix is now part of the official codebase

#### Patient-to-Patient Relationships Feature
- **Description**: Custom feature allowing linking patients to each other (e.g., family relationships)
- **Files added**:
  - `src/Services/PatientRelationshipService.php`
  - `src/Entity/PatientRelationship.php`
  - `src/Patient/Cards/PatientRelationshipViewCard.php`
  - `interface/patient_file/summary/patient_relationships_ajax.php`
  - `templates/patient/card/patient_relationships.html.twig`
  - `sql/odovita_patient-to-patient-relationships.sql`
- **Files modified**:
  - `interface/patient_file/summary/demographics.php` - Added card registration
  - `src/Common/Uuid/UuidRegistry.php` - Added `patient_relationships` table entry
- **Status**: Custom feature, not submitted upstream

#### Docker Development Fixes
- **Description**: Added volume mounts to prevent permission/rsync errors during development
- **Files modified**:
  - `docker/development-easy/docker-compose.yml`
  - `docker/development-easy-light/docker-compose.yml`
- **Changes**: Added `couchdbvolume` and `.git/objects` volume mount
- **Status**: Development convenience, may or may not be needed depending on upstream changes

### Branch Management

- **Backup branch**: `production-rel-7_0_3_4-squashed` contains the 4 squashed commits from the v7.0.3 era
- **Production branches**: Named `production-rel-X_Y_Z` (e.g., `production-rel-7_0_4`)
- Always check if fixes have been merged upstream before cherry-picking
