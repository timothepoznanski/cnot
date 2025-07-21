# GitHub Actions Workflows Configuration

This repository contains GitHub Actions workflows to automate production deployment with validation.

## Deployment workflow

### Pull Request approach with auto-merge (fully automated)
- **Trigger** : Push to `dev` branch
- **Action** : Automatic PR creation `dev` → `main` and immediate auto-merge
- **Deployment** : Automatic deployment to production
- **Benefits** : Fully automated process, faster deployment
- **Safety** : Automated tests + conflict detection prevent problematic deployments

## Required configuration

You must configure the following secrets in your GitHub repository (Settings → Secrets and variables → Actions) :

### Required secrets
- `PROD_HOST` : Production server IP address or domain name
- `PROD_USERNAME` : SSH username for server connection
- `PROD_SSH_KEY` : Private SSH key for authentication
- `PROD_SSH_PASSPHRASE` : SSH key passphrase (if your key has one)
- `PROD_PORT` : SSH port (usually 22)
- `PROD_PROJECT_PATH` : Absolute path to project on server (ex: `/root/cnot/cnot`)
- `PAT_TOKEN` : GitHub Personal Access Token to create Pull Requests

### SSH Configuration
1. Generate an SSH key pair on your production server:
   ```bash
   ssh-keygen -t rsa -b 4096 -C "github-actions@yourdomain.com"
   ```

2. Add the public key to the target user's `~/.ssh/authorized_keys` file

3. Copy the private key to the `PROD_SSH_KEY` secret

4. **If your key has a passphrase**: Add the passphrase to the `PROD_SSH_PASSPHRASE` secret

### GitHub Personal Access Token Configuration
1. Go to GitHub → Settings → Developer settings → Personal access tokens → Tokens (classic)
2. Click "Generate new token (classic)"
3. Give the token a name (ex: "CnoT Auto Deploy")
4. Select the following permissions:
   - ✅ `repo` (Full control of private repositories)
   - ✅ `workflow` (Update GitHub Action workflows)
5. Click "Generate token"
6. Copy the token and add it to the `PAT_TOKEN` secret

### Alternative option: Key without passphrase
If you prefer, you can create a dedicated SSH key without passphrase for GitHub Actions:
```bash
ssh-keygen -t rsa -b 4096 -f ~/.ssh/github_actions_key -N ""
```
In this case, you don't need the `PROD_SSH_PASSPHRASE` secret.

## Usage

The workflow works automatically:

1. **Push to `dev`** → Automatic PR creation and merge to `main`
2. **Automatic deployment** to production

### Workflow process
1. Develop on the `dev` branch
2. Push your changes: `git push origin dev`
3. **Conflict detection** - Checks if dev can merge cleanly into main
4. **Automated tests run** (PHP syntax, Docker build, etc.)
5. **If no conflicts AND tests pass**: A PR will be automatically created and merged (`dev` → `main`)
6. **If conflicts OR tests fail**: No PR is created, deployment is blocked
7. Deployment starts automatically to production

**Note**: The deployment is now fully automated with multiple safety checks: conflict detection + automated testing.

## File structure

```
.github/
└── workflows/
    ├── tests.yml                      # Automated tests (PHP, Docker, etc.)
    ├── auto-pr-production.yml         # Auto PR creation dev → main (after tests pass)
    ├── production-deployment.yml      # Deployment after merge to main
    └── README.md                      # This file
```

## Customization

You can modify the workflows according to your needs:
- **Add more tests** in `tests.yml` (database tests, API tests, etc.)
- Modify Docker commands according to your configuration
- Add notifications (Slack, Discord, email)
- Configure staging environments
- Add security scans or dependency checks

### Available tests in `tests.yml`:
- ✅ **Docker build test** - Ensures the application can be built
- ✅ **Docker Compose validation** - Checks compose file syntax
- ✅ **PHP syntax check** - Validates all PHP files
- ✅ **File permissions check** - Security validation
- ✅ **Application structure check** - Ensures required files exist

### Additional safety checks in `auto-pr-production.yml`:
- ✅ **Merge conflict detection** - Prevents auto-merge if conflicts exist when merging dev → main
- ✅ **Test completion verification** - Waits for tests to pass before proceeding
- ✅ **Token validation** - Ensures proper authentication setup

### Adding custom tests:
You can add more tests to `tests.yml` such as:
- Database connection tests
- API endpoint testing
- Security vulnerability scans
- Performance tests
- Code quality checks (PHPStan, etc.)

## Troubleshooting

### Error "refusing to merge unrelated histories"
This error occurs when `dev` and `main` branches were created independently:
- ✅ **This is normal for new repositories** - the workflow handles this automatically
- ✅ **The system will use `--allow-unrelated-histories`** for the conflict check
- ✅ **No action needed** - the workflow adapts to this situation

### Error "Merge conflicts detected"
This error occurs when your `dev` branch has changes that conflict with `main` during merge:
- ✅ **First, update your dev branch with latest main**: `git checkout dev && git pull origin main --allow-unrelated-histories` (add flag if needed)
- ✅ **Resolve conflicts manually** in your code editor  
- ✅ **Commit the resolved conflicts**: `git add . && git commit -m "Resolve merge conflicts"`
- ✅ **Push again**: `git push origin dev`
- ✅ **The workflow will automatically retry** the merge process

### Error "invalid header field value for Authorization"
This error indicates a problem with the Personal Access Token:
- ✅ Check that the `PAT_TOKEN` secret is properly configured in your repository
- ✅ Make sure the token hasn't expired
- ✅ Verify that the token has `repo` and `workflow` permissions
- ✅ The token should not contain spaces or special characters at the beginning/end

### Tests failing
If automated tests fail and block deployment:
- ✅ **Check the Actions tab** on GitHub to see which test failed
- ✅ **Fix the issue** in your dev branch
- ✅ **Push the fix**: The workflow will run again automatically

### SSH connection error
- Check that the SSH key is properly configured
- Make sure the user has sudo permissions if necessary

### Docker error
- Check that Docker and Docker Compose are installed on the server
- Make sure the user is in the `docker` group

### Git error
- Check that the repository is cloned on the production server
- Make sure the `main` branch exists and is tracked
