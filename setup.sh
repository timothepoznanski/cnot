#!/bin/bash

# CnoT Installation Script
# This script sets up the CnoT environment and configures aliases

echo "🚀 Setting up CnoT environment..."

# Check if Docker is installed
if ! command -v docker &> /dev/null; then
    echo "❌ Docker is not installed. Please install Docker first."
    exit 1
fi

# Check if Docker Compose is installed
if ! command -v docker compose &> /dev/null; then
    echo "❌ Docker Compose is not installed. Please install Docker Compose first."
    exit 1
fi

# Check if Git is installed
if ! command -v git &> /dev/null; then
    echo "❌ Git is not installed. Please install Git first."
    exit 1
fi

echo "✅ Prerequisites check passed"

# Load aliases
echo "📝 Loading CnoT aliases..."
source ./cnot-aliases.sh

# Add aliases to bashrc for persistence (optional)
read -p "Do you want to add CnoT aliases to your ~/.bashrc for persistence? (y/n): " -n 1 -r
echo
if [[ $REPLY =~ ^[Yy]$ ]]; then
    echo "" >> ~/.bashrc
    echo "# CnoT aliases" >> ~/.bashrc
    echo "source $(pwd)/cnot-aliases.sh" >> ~/.bashrc
    echo "✅ Aliases added to ~/.bashrc"
fi

echo ""
echo "🎉 CnoT setup completed successfully!"
echo ""
echo "📖 Available commands:"
echo "🚀 CnoT Available Commands:"
echo "-------------------------"
echo "cnot-dev      - Start development environment (dev branch, ports 8087/8088)"
echo "cnot-prod     - Start production environment (main branch, ports 8077/8078)"
echo "cnot-status   - Show status of running containers and networks"
echo "cnot-stop     - Stop all CnoT environments"
echo "cnot-cleanup  - Clean up unused Docker resources"
echo "cnot-help     - Show this help message"
echo ""
echo "📖 For more information, check the README.md file"
echo ""
echo "⚠️  To activate aliases in current session, run:"
echo "   source cnot-aliases.sh"
echo ""
echo "🚀 To get started, run: cnot-dev (for development) or cnot-prod (for production)"
