# GitHub Push Instructions

Follow these steps to push the Dynamic Post Filter plugin to GitHub:

## Step 1: Create a GitHub Repository

1. Go to [github.com](https://github.com)
2. Click **New Repository** (green button)
3. Enter repository name: `dynamic-post-filter`
4. Add description: "Display posts and custom post types with dynamic AJAX taxonomy filtering and pagination"
5. Choose **Public** (for open-source)
6. ✅ Check "Add a README file" (already have one)
7. ✅ Check "Add .gitignore" (just created)
8. Choose License: **GPL v2.0**
9. Click **Create Repository**

## Step 2: Initialize Git Locally

Open PowerShell or Command Prompt in the plugin folder:

```powershell
cd c:\Users\daric\OneDrive\Desktop\all-menu-filter
```

Initialize git:

```powershell
git init
```

Add all files:

```powershell
git add .
```

Create initial commit:

```powershell
git commit -m "Initial commit: Dynamic Post Filter plugin"
```

## Step 3: Connect to GitHub Remote

Copy the repository URL from GitHub (looks like `https://github.com/username/dynamic-post-filter.git`)

Set the remote:

```powershell
git remote add origin https://github.com/username/dynamic-post-filter.git
```

Replace `username` with your actual GitHub username.

## Step 4: Push to GitHub

Push the main branch:

```powershell
git branch -M main
git push -u origin main
```

## Done! 🎉

Your plugin is now on GitHub. You can view it at:
`https://github.com/username/dynamic-post-filter`

---

## Common Git Commands for Future Updates

### Push new changes:

```powershell
git add .
git commit -m "Your commit message"
git push
```

### View status:

```powershell
git status
```

### View commit history:

```powershell
git log
```

### Create a release/tag:

```powershell
git tag -a v1.0.0 -m "Version 1.0.0"
git push origin v1.0.0
```
