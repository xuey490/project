#!/bin/bash

set -e

# ============================================================================
# 全局变量定义
# ============================================================================
FORCE_CLOUDSTUDIO="${FORCE_CLOUDSTUDIO:-false}"
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PROJECT_ROOT="$(cd "$SCRIPT_DIR/../../.." && pwd)"
TEMPLATES_DIR="$PROJECT_ROOT/.templates"
TEMPLATE_NAME="web"  # 默认模板
SUB_PROJECT_TYPE=""  # 子项目类型

# Debug: 打印初始环境变量
print_debug_vars() {
    echo "========== Environment Variables =========="
    echo "FORCE_CLOUDSTUDIO: $FORCE_CLOUDSTUDIO"
    echo "SCRIPT_DIR: $SCRIPT_DIR"
    echo "PROJECT_ROOT: $PROJECT_ROOT"
    echo "TEMPLATES_DIR: $TEMPLATES_DIR"
    echo "TEMPLATE_NAME: $TEMPLATE_NAME"
    echo "SUB_PROJECT_TYPE: $SUB_PROJECT_TYPE"
    echo "==========================================="
}

# ============================================================================
# 辅助函数
# ============================================================================

# 打印项目根目录
print_project_info() {
    echo "Project root: $PROJECT_ROOT"
}

# 检查是否已经初始化
check_already_initialized() {
    if [ -f "$PROJECT_ROOT/.genie/config.json" ]; then
        exit 0
    fi
}

# 检查模板目录是否存在
validate_templates_dir() {
    if [ ! -d "$TEMPLATES_DIR" ]; then
        echo "❌ Error: templates directory not found at $TEMPLATES_DIR"
        exit 1
    fi
}

# 解析命令行参数
parse_arguments() {
    local positional_index=0
    while [[ $# -gt 0 ]]; do
        case $1 in
            --cloudstudio)
                FORCE_CLOUDSTUDIO=true
                shift
                ;;
            -*)
                echo "Unknown option: $1"
                shift
                ;;
            *)
                # 位置参数: 第一个是 TEMPLATE_NAME，第二个是 SUB_PROJECT_TYPE
                if [ $positional_index -eq 0 ]; then
                    TEMPLATE_NAME="$1"
                elif [ $positional_index -eq 1 ]; then
                    SUB_PROJECT_TYPE="$1"
                fi
                positional_index=$((positional_index + 1))
                shift
                ;;
        esac
    done
}

# 验证模板目录是否存在
validate_template() {
    local template_dir
    if [ -n "$SUB_PROJECT_TYPE" ]; then
        template_dir="$TEMPLATES_DIR/$SUB_PROJECT_TYPE"
    else
        template_dir="$TEMPLATES_DIR/$TEMPLATE_NAME"
    fi
    
    if [ ! -d "$template_dir" ]; then
        echo "❌ Error: Template directory not found at $template_dir"
        exit 1
    fi
    
    echo "📋 Using template: ${SUB_PROJECT_TYPE:-$TEMPLATE_NAME}"
}

# 检测并处理环境特定配置
process_env_config() {
    local env_type="common"
    
    if [ "$FORCE_CLOUDSTUDIO" = true ]; then
        env_type="cloudstudio"
        echo "🔧 Using Cloud Studio environment (manually specified)"
    elif [ -f "/run/cloudstudio/space.yaml" ]; then
        env_type="cloudstudio"
        echo "🔧 Auto-detected Cloud Studio environment"
    else
        echo "🔧 Using local development environment"
    fi
    
    python3 "$PROJECT_ROOT/.genie/scripts/python/process_env_template.py" "$env_type"
}

# 解析软链接并复制实际内容
# 参数: $1=源路径, $2=目标路径, $3=名称
copy_and_resolve_symlink() {
    local src="$1"
    local dest="$2"
    local name="$3"

    if [ -L "$src" ]; then
        local real_path=$(readlink "$src")
        if [[ "$real_path" != /* ]]; then
            real_path="$(cd "$(dirname "$src")" && cd "$real_path" && pwd)"
        fi

        if [ -d "$real_path" ]; then
            echo "📦 Copying $name (resolved from symlink)..."
            # 如果目标已存在，复制内容并覆盖同名文件，保留其他文件
            if [ -d "$dest" ]; then
                cp -Rf "$real_path"/* "$dest"/
            else
                cp -R "$real_path" "$dest"
            fi
            install_npm_dependencies "$dest" "$name"
            echo "✅ $name created"
        else
            echo "⚠️  Warning: symlink target not found for $name, skipping"
        fi
    elif [ -d "$src" ]; then
        echo "📦 Copying $name..."
        # 如果目标已存在，复制内容并覆盖同名文件，保留其他文件
        if [ -d "$dest" ]; then
            cp -Rf "$src"/* "$dest"/
        else
            cp -R "$src" "$dest"
        fi
        install_npm_dependencies "$dest" "$name"
        echo "✅ $name created"
    fi
}

# 安装 npm 依赖(如果存在 package.json)
install_npm_dependencies() {
    local dir="$1"
    local name="$2"
    
    if [ -f "$dir/package.json" ]; then
        echo "📦 Installing $name dependencies (background)..."
        nohup bash -c "cd $dir && npm audit fix && npm install --production=false" > /dev/null 2>&1 &
    fi
}

# 合并 .cloudstudio 配置文件
merge_cloudstudio_config() {
    local template_dir
    if [ -n "$SUB_PROJECT_TYPE" ]; then
        template_dir="$TEMPLATES_DIR/$SUB_PROJECT_TYPE"
    else
        template_dir="$TEMPLATES_DIR/$TEMPLATE_NAME"
    fi
    local template_cloudstudio="$template_dir/.cloudstudio"
    local current_cloudstudio="$PROJECT_ROOT/.cloudstudio"
    
    if [ -f "$template_cloudstudio" ]; then
        echo "📝 Processing .cloudstudio configuration..."
        
        # 检查当前项目是否已有 .cloudstudio 文件
        if [ -f "$current_cloudstudio" ]; then
            # 存在则合并：当前文件内容 + 两个换行 + 模板文件内容
            local temp_output=$(mktemp)
            cat "$current_cloudstudio" > "$temp_output"
            echo "" >> "$temp_output"
            echo "" >> "$temp_output"
            cat "$template_cloudstudio" >> "$temp_output"
            
            mv "$temp_output" "$current_cloudstudio"
            echo "✅ .cloudstudio configuration merged successfully"
        else
            # 不存在则直接使用模板文件
            cp "$template_cloudstudio" "$current_cloudstudio"
            echo "✅ .cloudstudio configuration created from template"
        fi
    fi
}

# 合并 CODEBUDDY.md 文件
merge_codebuddy_file() {
    local src="$1"
    local dest="$2"
    
    local current_content=""
    if [ -f "$dest" ]; then
        echo "📄 Backing up existing CODEBUDDY.md content..."
        current_content=$(cat "$dest")
    fi

    cp "$src" "$dest"

    if [ -n "$current_content" ]; then
        echo "📄 Merging existing CODEBUDDY.md content..."
        local temp_file=$(mktemp)
        local backup_file=$(mktemp)

        echo "$current_content" > "$backup_file"
        
        sed -e "/\[Project Rules\]/r $backup_file" -e '/\[Project Rules\]/d' \
            "$dest" > "$temp_file"
        
        mv "$temp_file" "$dest"
        rm -f "$backup_file"
    fi

    echo "✅ CODEBUDDY.md created and merged"
}

# 复制模板文件到项目根目录
copy_template_files() {
    local template_dir
    if [ -n "$SUB_PROJECT_TYPE" ]; then
        template_dir="$TEMPLATES_DIR/$SUB_PROJECT_TYPE"
    else
        template_dir="$TEMPLATES_DIR/$TEMPLATE_NAME"
    fi
    
    for item in "$template_dir"/* "$template_dir"/.*; do
        if [ ! -e "$item" ]; then
            continue
        fi

        local item_name=$(basename "$item")
        
        # 跳过 . 和 ..
        if [ "$item_name" = "." ] || [ "$item_name" = ".." ]; then
            continue
        fi
        local dest_path="$PROJECT_ROOT/$item_name"

        # 跳过已特殊处理的 .cloudstudio
        if [ "$item_name" = ".cloudstudio" ]; then
            continue
        fi

        # 处理目录(包括软链接)
        if [ -d "$item" ] || [ -L "$item" ]; then
            copy_and_resolve_symlink "$item" "$dest_path" "$item_name"
        # 处理文件
        elif [ -f "$item" ]; then
            if [ "$item_name" = "CODEBUDDY.md" ]; then
                cp "$item" "$dest_path"
            else
                echo "📄 Copying $item_name..."
                cp "$item" "$dest_path"
                echo "✅ $item_name created"
            fi
        fi
    done
}

# 移动配置文件到 .genie 目录
move_config_file() {
    if [ -f "$TEMPLATES_DIR/config.json" ]; then
        mv "$TEMPLATES_DIR/config.json" "$PROJECT_ROOT/.genie/config.json"
        echo "✅ Config file moved to .genie/"
    fi
}

# 设置项目信息
setup_project_info() {
    local project_type="$1"
    local sub_project_type="$2"
    local docs_dir="$PROJECT_ROOT/docs"
    local project_json="$docs_dir/project.json"
    
    echo "📝 Setting up project info..."
    
    # 创建 docs 目录（如果不存在）
    mkdir -p "$docs_dir"
    
    # 读取现有数据（如果文件存在）
    local existing_project_type=""
    local existing_sub_project_type=""
    
    if [ -f "$project_json" ]; then
        if command -v jq >/dev/null 2>&1; then
            existing_project_type=$(jq -r '.project_type // ""' "$project_json" 2>/dev/null || echo "")
            existing_sub_project_type=$(jq -r '.sub_project_type // ""' "$project_json" 2>/dev/null || echo "")
        fi
    fi
    
    # 合并数据：优先使用新值，否则保留现有值
    local final_project_type="${project_type:-${existing_project_type}}"
    local final_sub_project_type="${sub_project_type:-${existing_sub_project_type}}"
    
    # 写入 JSON 文件
    cat > "$project_json" <<EOF
{
  "project_type": "$final_project_type",
  "sub_project_type": "$final_sub_project_type"
}
EOF
    
    echo "✅ Project info saved to docs/project.json"
}

# 清理临时模板目录
cleanup_templates_dir() {
    rm -rf "$TEMPLATES_DIR"
    echo "🎉 Project setup completed!"
}

# 启动项目服务
start_project_services() {
    local process_script="$PROJECT_ROOT/.genie/scripts/node/process"
    
    if [ -f "$process_script" ] && [ -f "$PROJECT_ROOT/.cloudstudio" ]; then
        echo "🚀 Starting project services..."
        "$process_script" start --restart
        
        if [ $? -eq 0 ]; then
            echo "✅ Services started successfully"
        else
            echo "⚠️  Warning: Failed to start services, you can start them manually with: ./.genie/scripts/node/process start"
        fi
    fi
}

# ============================================================================
# 主流程
# ============================================================================
main() {
    # 1. 打印项目信息
    print_project_info
    
    # 2. 检查是否已初始化
    check_already_initialized
    
    # 3. 开始设置提示
    echo "📦 Setting up project from templates..."
    
    # 4. 验证模板目录存在
    validate_templates_dir
    
    # 5. 解析命令行参数
    parse_arguments "$@"
    
    # Debug: 打印解析后的变量
    print_debug_vars
    
    # 6. 验证指定的模板
    validate_template
    
    # 7. 处理环境特定配置
    # process_env_config
    
    # 8. 合并 .cloudstudio 配置
    merge_cloudstudio_config
    
    # 9. 复制模板文件到项目
    copy_template_files
    
    # 10. 移动配置文件
    move_config_file
    
    # 11. 设置项目信息
    setup_project_info "$TEMPLATE_NAME" "$SUB_PROJECT_TYPE"
    
    # 12. 清理临时目录
    cleanup_templates_dir
    
    # 13. 启动项目服务
    start_project_services
}

# 执行主流程
main "$@"
