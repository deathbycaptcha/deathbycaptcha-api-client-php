# GitHub Actions Configuration

This project is configured to automatically run tests on GitHub Actions.

## Configuration

File: `.github/workflows/tests.yml`

## PHP Versions Tested

### A. Automatic Tests on LTS Versions

| Version | Runner | Status | Notes |
|---------|--------|--------|-------|
| PHP 7.4 | `ubuntu-latest` | ✅ Required | Last 7.x version |
| PHP 8.1 | `ubuntu-latest` | ✅ Required | LTS 8.x version |
| PHP 8.3 | `ubuntu-latest` | ✅ Required | Latest stable |

### B. Additional Analysis (Optional)

| Job | PHP Version | Purpose |
|-----|------------|---------|
| `coverage` | 8.3 | Code coverage analysis with percentage badge generation|
| `lint` | 8.3 | PHP syntax validation |
| `phpstan` | 8.3 | Static analysis |

## Execution Flow

```
┌─────────────────────────────────────────────────┐
│     Push/Pull Request to Repository              │
└──────────────────┬──────────────────────────────┘
                   │
        ┌──────────┴──────────┐
        │                     │
    ┌───▼────┐          ┌────▼────┐
    │  Test  │          │Analysis │
    │  Jobs  │          │  Jobs   │
    └───┬────┘          └────┬────┘
        │                    │
    ┌───┴────────────┬───────┴─────┬──────────┐
    │                │              │          │
 ┌──▼──┐         ┌──▼──┐       ┌───▼───┐  ┌──▼────┐
 │PHP  │         │PHP  │       │ Lint  │  │PHPStan│
 │7.4  │         │8.1  │       │       │  │       │
 └─────┘         └─────┘       └───────┘  └───────┘
    ✓               ✓              ✓          ✓
    │               │              │          │
 ┌──▼──┐       ┌───▼────┐         │          │
 │PHP  │       │Coverage│         │          │
 │8.3  │       │ Report │         │          │
 └─────┘       └────────┘         │          │
    ✓               ✓              │          │
    └───────────────┴──────────────┴──────────┘
          All Required Tests Pass
```

## Included Jobs

### 1. test-php74
```yaml
name: "PHP 7.4 LTS Tests"
runs-on: ubuntu-latest
steps:
  - Setup PHP 7.4
  - Install PHPUnit 9.5
  - Run tests
```
- **Purpose**: Verify compatibility with PHP 7.4
- **Requirement**: MUST pass
- **Artifacts**: test-results-php74 (7 days retention)
- **Special**: Uses PHPUnit 9.5 for PHP 7.4 compatibility

### 2. test-php81
```yaml
name: "PHP 8.1 LTS Tests"
runs-on: ubuntu-latest
steps:
  - Setup PHP 8.1
  - Install dependencies
  - Run tests
```
- **Purpose**: Verify compatibility with PHP 8.1 (LTS)
- **Requirement**: MUST pass
- **Artifacts**: test-results-php81 (7 days retention)

### 3. test-php83
```yaml
name: "PHP 8.3 LTS Tests"
runs-on: ubuntu-latest
steps:
  - Setup PHP 8.3
  - Install dependencies
  - Run tests
```
- **Purpose**: Verify compatibility with PHP 8.3 (latest stable)
- **Requirement**: MUST pass
- **Artifacts**: test-results-php83 (7 days retention)

### 4. coverage
```yaml
name: "Code Coverage (PHP 8.3)"
if: github.ref == 'refs/heads/main' || github.ref == 'refs/heads/master'
```
- **Purpose**: Generate detailed coverage report with percentage calculation
- **Coverage Calculation**: Extracts lines covered vs valid lines from phpunit XML output
- **Percentage Color**: Badge color changes based on coverage percentage
  - 🟢 >= 80%: brightgreen
  - 🟢 >= 70%: green  
  - 🟡 >= 60%: yellowgreen
  - 🟡 >= 50%: yellow
  - 🔴 < 50%: red
- **Requirement**: OPTIONAL (continue-on-error: true)
- **Artifacts**: coverage-report (includes HTML report, XML, and badge.json) - 30 days retention
- **Trigger**: Only on `main` or `master` branches
- **Analysis**: Uses Xdebug for coverage measurement
- **Outputs**:
  - `coverage/index.html` - Interactive HTML coverage report
  - `coverage.xml` - Clover XML format for tools integration
  - `.coverage/badge.json` - JSON badge with calculated coverage percentage and color
  - Log output shows "Coverage: XX%" message

### 5. lint
```yaml
name: "PHP Syntax Check"
continue-on-error: true
```
- **Purpose**: Validate PHP syntax in all files
- **Requirement**: OPTIONAL
- **Trigger**: All pushes and PRs

### 6. phpstan
```yaml
name: "PHPStan Static Analysis"
continue-on-error: true
if: github.ref == 'refs/heads/main' || github.ref == 'refs/heads/master'
```
- **Purpose**: Static code analysis
- **Requirement**: OPTIONAL
- **Trigger**: Only on `main` or `master` branches

## How GitHub Actions Activates

### Requirements

1. ✅ Project on GitHub.com or GitHub Enterprise
2. ✅ `.github/workflows/tests.yml` file in repository
3. ✅ GitHub Actions enabled for the repository (enabled by default)

### Automatic Activation

Workflow runs automatically:
- ✅ On every `git push` to main/master/develop branches
- ✅ On Pull Requests targeting main/master/develop
- ✅ Manual trigger via "Run workflow" button

### Manual Activation

1. Go to **Repository > Actions tab**
2. Select **"Tests"** workflow
3. Click **"Run workflow"** button
4. Select the branch
5. Click **"Run workflow"** to start

## Environment Variables

Configured:
```yaml
env:
  COMPOSER_FLAGS: --no-progress --no-interaction
```

You can add repository secrets in **Settings > Secrets and variables > Actions**

## Optimizations

### Composer Cache
- ✅ Caches Composer dependencies per PHP version
- ✅ Significantly speeds up workflow runs
- ✅ Automatic cache invalidation when composer.json changes

### Parallel Execution
- ✅ All test jobs run in parallel
- ✅ Faster feedback than sequential execution
- ✅ Independent job failures don't block others

### PHP Setup
- Uses `shivammathur/setup-php@v2` action for fast PHP installation
- Includes extensions: `curl`, `json`, `xdebug` (for coverage)
- Pre-configured with Composer v2

## Generated Artifacts

### Per test job:
- `test-results-phpXX` - Test results for each PHP version
- **Retention**: 7 days

### Coverage job:
- `coverage-report/` - HTML coverage report
- `coverage.xml` - Clover coverage XML
- **Retention**: 30 days
- **Upload**: Automatically uploaded to Codecov

## Viewing Results

### 1. Workflow Status
On GitHub:
- **Repository > Actions tab**
- See all workflow runs with status

### 2. Job Details
- Click on a workflow run
- Click on individual jobs to see logs
- View status of each test

### 3. Coverage Report
- Download `coverage-report` artifact from the workflow run
- **View Coverage Percentage**:
  1. Go to workflow run details
  2. Scroll to "Artifacts" section
  3. Download `coverage-report` artifact
  4. Open `.coverage/badge.json` to see coverage percentage
  5. Or open `coverage/index.html` in a browser for interactive report
- **Coverage Summary**:
  - Check job logs in "Code Coverage (PHP 8.3)" job
  - Look for "Coverage: XX%" in the output

### 4. Test Results
- Download test artifacts from completed runs
- View in Actions summary page

## Integrations

### Status Badge in README.md

Add badges to your `README.md`:

```markdown
## CI/CD Status

[![Tests](https://github.com/deathbycaptcha/deathbycaptcha-api-client-php/actions/workflows/tests.yml/badge.svg)](https://github.com/deathbycaptcha/deathbycaptcha-api-client-php/actions/workflows/tests.yml)
[![Coverage Report](https://img.shields.io/badge/coverage-view%20report-blue)](https://github.com/deathbycaptcha/deathbycaptcha-api-client-php/actions/workflows/tests.yml)
```

### Coverage Badge and Reports

Coverage reports are automatically generated on every push to main/master:
1. **Coverage Percentage**: Calculated from PHPUnit's clover XML output
2. **Badge Color**: Automatically assigned based on coverage level
   - 🟢 80%+: brightgreen | 70-79%: green | 60-69%: yellowgreen | 50-59%: yellow | <50%: red
3. **Access Reports**:
   - Download `coverage-report` artifact from workflow run
   - View HTML report: `coverage/index.html`
   - Check badge data: `.coverage/badge.json`
   - See percentage in job logs

### Branch Protection Rules

Recommended settings in **Settings > Branches > Branch protection rules**:
- ✅ Require status checks to pass before merging
- ✅ Select status checks:
  - `test-php74`
  - `test-php81`
  - `test-php83`
- ✅ Require branches to be up to date before merging

## Differences from GitLab CI

| Feature | GitLab CI | GitHub Actions |
|---------|-----------|----------------|
| Config file | `.gitlab-ci.yml` | `.github/workflows/tests.yml` |
| Runners | Docker images | Ubuntu runners with setup-php |
| Caching | Built-in | Uses actions/cache |
| Artifacts | 1-30 days | 1-90 days configurable |
| Parallel | By default | Matrix or separate jobs |
| Triggers | push/MR | push/PR/workflow_dispatch |

### Advantages of GitHub Actions

- ✅ **Composer caching** - Much faster dependency installation
- ✅ **Matrix builds** - Easy to test multiple PHP versions
- ✅ **Marketplace** - 1000s of pre-built actions
- ✅ **Native integration** - Deep GitHub integration
- ✅ **Free minutes** - 2000-3000 minutes/month on free tier

## Troubleshooting

### "Workflow not running"

1. Check that Actions are enabled: **Settings > Actions > General**
2. Verify the workflow file is in `.github/workflows/` directory
3. Check YAML syntax is valid
4. Ensure you have push access to the repository
5. Check that the default branch is `main` or `master`

### "Tests failing on GitHub but pass locally"

1. Check PHP version matches (`php -v`)
2. Verify all required extensions are installed
3. Check environment variables
4. Review detailed logs in Actions tab

### "Coverage report missing or percentage not calculated"

1. Ensure coverage job ran successfully (check workflow logs)
2. Verify `coverage.xml` was generated from PHPUnit
3. Check that `.coverage/badge.json` exists in artifacts
4. Review "Extract coverage percentage" step in logs
5. Ensure Xdebug was properly enabled in coverage job

## Cost Considerations

GitHub Actions includes:
- **Public repositories**: Unlimited minutes
- **Private repositories**: 
  - Free: 2000 minutes/month
  - Pro: 3000 minutes/month
  - Team/Enterprise: 50,000+ minutes/month

Each workflow run (3 test jobs + coverage + lint + phpstan) takes approximately:
- **~5-8 minutes** total (jobs run in parallel)
- **~2-3 minutes** billed time per PHP version test job
- **~5-6 minutes** billed time for coverage job

## Next Steps

1. Push this configuration to your repository
2. Navigate to **Actions** tab to see workflow runs
3. Add status badge to README.md
4. Set up branch protection rules
5. Monitor test results and coverage reports
6. Download coverage reports from workflow artifacts

## Support

- **GitHub Actions Documentation**: https://docs.github.com/en/actions
- **setup-php Action**: https://github.com/shivammathur/setup-php
- **PHPUnit Documentation**: https://docs.phpunit.de/
- **Composer**: https://getcomposer.org/doc
5. Set up branch protection rules
5. Monitor test results and coverage on every push

## Support

- **GitHub Actions Documentation**: https://docs.github.com/en/actions
- **setup-php Action**: https://github.com/shivammathur/setup-php
- **PHPUnit Documentation**: https://docs.phpunit.de/
- **Composer**: https://getcomposer.org/doc
