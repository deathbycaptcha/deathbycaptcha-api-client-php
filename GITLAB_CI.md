# GitLab CI/CD Configuration

This project is configured to automatically run tests on GitLab CI/CD.

## Configuration

File: `.gitlab-ci.yml`

### Defined Stages

1. **test** - Runs tests on multiple PHP versions
2. **coverage** - Generates code coverage report

## PHP Versions Tested

### A. Automatic Tests on LTS Versions

| Version | Docker Image | Status | Notes |
|---------|--------------|--------|-------|
| PHP 7.4 | `php:7.4-cli` | ✅ Required | Last 7.x version |
| PHP 8.1 | `php:8.1-cli` | ✅ Required | LTS 8.x version |
| PHP 8.3 | `php:8.3-cli` | ✅ Required | Latest stable |

### B. Additional Analysis (Optional)

| Job | PHP Version | Purpose |
|-----|------------|---------|
| `coverage` | 8.3 | Code coverage analysis (Xdebug) |
| `lint:php` | 8.3 | PHP syntax validation |
| `phpstan` | 8.3 | Static analysis |

## Execution Flow

```
┌─────────────────────────────────────────────────┐
│          Push to Repository                      │
└──────────────────┬──────────────────────────────┘
                   │
        ┌──────────┴──────────┐
        │                     │
    ┌───▼────┐          ┌────▼────┐
    │ Stage: │          │  Stage: │
    │  test  │          │ coverage│
    └───┬────┘          └────┬────┘
        │                    │
    ┌───┴──────────────┬─────────┐
    │                  │         │
 ┌──▼──┐          ┌──────┐  ┌──────┐
 │PHP  │          │PHP   │  │PHP   │
 │7.4  │          │8.1   │  │8.3   │
 └─────┘          └──────┘  └──────┘
    ✓                ✓         ✓
    │                │         │
    └────────────────┴─────────┘
        All Tests Must Pass
```

## Included Jobs

### 1. test:php7.4
```yaml
stage: test
image: php:7.4-cli
script: ./vendor/bin/phpunit --no-coverage --testdox
```
- **Purpose**: Verify compatibility with PHP 7.4
- **Requirement**: MUST pass
- **Artifacts**: junit.xml, coverage.xml

### 2. test:php8.1
```yaml
stage: test
image: php:8.1-cli
script: ./vendor/bin/phpunit --no-coverage --testdox
```
- **Purpose**: Verify compatibility with PHP 8.1 (LTS)
- **Requirement**: MUST pass
- **Artifacts**: junit.xml, coverage.xml

### 3. test:php8.3
```yaml
stage: test
image: php:8.3-cli
script: ./vendor/bin/phpunit --no-coverage --testdox
```
- **Purpose**: Verify compatibility with PHP 8.3 (latest stable)
- **Requirement**: MUST pass
- **Artifacts**: junit.xml, coverage.xml

### 4. coverage
```yaml
stage: coverage
image: php:8.3-cli
script: ./vendor/bin/phpunit --coverage-text --coverage-clover=coverage.xml --coverage-html=coverage
```
- **Purpose**: Generate detailed coverage report
- **Requirement**: OPTIONAL (allow_failure: true)
- **Artifacts**: coverage/, coverage.xml
- **Trigger**: Only on `main` or `master` branches
- **Analysis**: Uses Xdebug for coverage measurement

### 5. lint:php
```yaml
stage: test
script: find . -path ./vendor -prune -o -type f -name '*.php' -print0 | xargs -0 -I {} php -l {}
```
- **Purpose**: Validate PHP syntax in all files
- **Requirement**: OPTIONAL (allow_failure: true)
- **Trigger**: All pushes

### 6. phpstan
```yaml
stage: test
script: ./vendor/bin/phpstan analyse deathbycaptcha.php
```
- **Purpose**: Static code analysis
- **Requirement**: OPTIONAL (allow_failure: true)
- **Trigger**: Only on `main` or `master` branches

## How GitLab CI/CD Activates

### Requirements

1. ✅ Project on GitLab.com or private GitLab instance
2. ✅ GitLab Runner available (public runners from GitLab by default)
3. ✅ `.gitlab-ci.yml` file in root directory of repository

### Automatic Activation

CI/CD runs automatically:
- ✅ On every `git push`
- ✅ On Pull Requests / Merge Requests
- ✅ According to configured triggers

### Manual Activation

1. Go to **Project > CI/CD > Pipelines**
2. Click **"Run Pipeline"**
3. Select the branch
4. Click **"Create pipeline"**

## Environment Variables

Configured:
```yaml
COMPOSER_MEMORY_LIMIT: -1
COMPOSER_FLAGS: "--no-progress --no-interaction"
```

You can add more in **Project > Settings > CI/CD > Variables**

## Generated Artifacts

### Per test job:
- `junit.xml` - JUnit format report (for integrations)
- `coverage.xml` - Clover XML coverage data

### Per coverage job:
- `coverage/` - HTML coverage report
- `coverage.xml` - Clover coverage XML

**Expiration**: 
- Tests: 1 week
- Coverage: 30 days

## Viewing Results

### 1. Pipeline Status
On GitLab, go to:
- **Project > CI/CD > Pipelines**

### 2. Job Details
- Click on a job to see full logs
- View status of each test

### 3. Coverage Report
- **Project > Analytics > Code Review**
- Or download `coverage/index.html` from artifacts

### 4. Test Results
- **Project > CI/CD > Test Reports**
- Graphical visualization of results

## Integrations

### Badge in README.md

Add badges to your `README.md`:

```markdown
## CI/CD Status

[![pipeline status](https://gitlab.com/your-group/your-project/badges/main/pipeline.svg)](https://gitlab.com/your-group/your-project/-/commits/main)
[![coverage report](https://gitlab.com/your-group/your-project/badges/main/coverage.svg)](https://gitlab.com/your-group/your-project/-/commits/main)
```

### Slack Notifications

In **Project > Integrations > Slack**:
1. Configure Slack webhook
2. Receive notifications on each pipeline

### Email Notifications

In **Project > Notifications**:
- Receive emails with test results

## Troubleshooting

### "No Runners available"
- Use GitLab's public runners (default)
- Or register a private runner: https://docs.gitlab.com/ee/ci/runners/

### "PHP extension missing"
- Edit `.gitlab-ci.yml`
- Install extensions in `before_script`:
  ```yaml
  before_script:
    - docker-php-ext-install curl
  ```

### "Composer not found"
- Pre-installed in the `before_script`
- Or use image with Composer: `php:8.3-fpm`

### Tests fail on CI but pass locally
- Verify you're using the same PHP version
- Check for environment variable differences
- Run: `php --version` on the server

## Optimizations

### Composer Caching
```yaml
cache:
  paths:
    - vendor/
  key:
    files:
      - composer.lock
```

### Parallel Execution
The 5 test jobs run in parallel (same total time)

### Conditional Jobs
```yaml
only:
  - main
  - merge_requests
```

## Example Output

### Logs from a successful job:
```
PHPUnit 10.5.63 by Sebastian Bergmann and contributors.

Runtime:       PHP 8.3.29
Tests: 74, Assertions: 158
✓ OK, All tests passed
Time: 3.3 seconds
```

### Coverage badge:
```
Coverage: 85%
```

## Resources

- 📖 [GitLab CI/CD Documentation](https://docs.gitlab.com/ee/ci/)
- 📖 [PHPUnit Documentation](https://phpunit.de/)
- 🐳 [Docker Hub PHP Images](https://hub.docker.com/_/php)
- 📊 [Clover Coverage Format](https://github.com/openclover/clover)

## Next Steps

1. Push changes to GitLab
2. Go to **CI/CD > Pipelines** and watch execution
3. Download coverage artifacts when done
4. Integrate badges into README.md

---

**CI/CD configuration ready for multiple PHP versions** ✅
