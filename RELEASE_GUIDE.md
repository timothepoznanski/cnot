# 🏷️ Release Management Guide

## 🎯 Problem Solved
This system addresses the common issue: "I push small bug fixes to `dev` frequently, but I don't want to create a release for every single change."

## 🚀 How It Works

### **Normal Development Workflow**
```bash
# Regular development - NO release created
git add .
git commit -m "Fix small bug in navigation"
git push origin dev
# Merge PR to main → Only timestamp deployment (202501241530)
```

### **When You Want a Real Release**
```bash
# Option 1: Auto-versioned release
git add .
git commit -m "Add new feature [release]"
git push origin dev
# Merge PR to main → Creates v1.0.202501241530 + GitHub release

# Option 2: Specific version release
git add .
git commit -m "Major update [version: 1.2.0]"
git push origin dev
# Merge PR to main → Creates v1.2.0 + GitHub release
```

## 📋 Version Types

| Commit Message | Result | Interface Display |
|----------------|--------|-------------------|
| `"Fix bug"` | Timestamp only | Version: 24/01/2025 15:30 |
| `"Fix bug [release]"` | v1.0.202501241530 + GitHub release | Release: v1.0.202501241530 |
| `"New feature [version: 1.2.0]"` | v1.2.0 + GitHub release | Release: v1.2.0 |

## 🎮 Usage Examples

### Small Bug Fixes (Most Common)
```bash
git commit -m "Fix typo in error message"
git commit -m "Update CSS styling"
git commit -m "Optimize database query"
# → Only timestamp deployment, no release spam
```

### Feature Releases
```bash
git commit -m "Add dark mode support [version: 1.1.0]"
git commit -m "Major UI overhaul [version: 2.0.0]"
git commit -m "Security updates [release]"  # Auto-version
# → Creates proper GitHub releases
```

## 🏗️ Docker Tags Created

### Normal Deployment
- `timpoz/cnot:202501241530` (timestamp)
- `timpoz/cnot:latest`

### Release Deployment
- `timpoz/cnot:202501241530` (timestamp)
- `timpoz/cnot:v1.2.0` (release version)
- `timpoz/cnot:latest`

## 📱 Interface Display

The application automatically detects the version type:
- **Timestamp**: Shows "Version: 24/01/2025 15:30"
- **Release**: Shows "Release: v1.2.0"
- **Development**: Shows "Version: Development"

## 🔄 Rollback

### To a timestamp
```bash
DOCKER_IMAGE=timpoz/cnot:202501241530 docker compose up -d --force-recreate
```

### To a release
```bash
DOCKER_IMAGE=timpoz/cnot:v1.2.0 docker compose up -d --force-recreate
```

## 💡 Best Practices

1. **Use semantic versioning** for releases: `1.2.3` (major.minor.patch)
2. **Reserve `[release]`** for important updates without specific version
3. **Most commits** should be normal (no release keywords)
4. **Group related changes** before creating a release

This way, you can develop freely without release spam, but still create proper releases when needed!
