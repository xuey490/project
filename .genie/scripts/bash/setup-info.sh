#!/bin/bash

set -e

# ============================================================================
# 全局变量定义
# ============================================================================
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PROJECT_ROOT="$(cd "$SCRIPT_DIR/../../.." && pwd)"
DOCS_DIR="$PROJECT_ROOT/docs"
PROJECT_JSON="$DOCS_DIR/project.json"

PROJECT_NAME=""
VERSION_DESCRIPTION=""
PREVIEW_TYPE="pc"  # Default preview type
PROJECT_TYPE=""    # Project type (e.g., web, backend, frontend)

# ============================================================================
# 辅助函数
# ============================================================================

# 显示帮助信息
show_help() {
    cat << EOF
Usage: setup_info.sh [--name PROJECT_NAME] [--preview-type PREVIEW_TYPE] [--project-type PROJECT_TYPE] VERSION_DESCRIPTION

Arguments:
  --name PROJECT_NAME           Project name (required for first run, optional afterwards)
  --preview-type PREVIEW_TYPE   Preview type: "pc" or "mobile" (default: "pc")
  --project-type PROJECT_TYPE   Project type: "web", "backend", "frontend", etc. (optional)
  VERSION_DESCRIPTION           Version description (required, must be the last argument)

Examples:
  # First time setup (--name is required)
  setup_info.sh --name "My Project" "Initial version"
  setup_info.sh --name "My Project" --preview-type mobile --project-type web "Initial version"
  
  # Update version description (--name is optional)
  setup_info.sh "Updated version with new features"
  setup_info.sh --preview-type mobile "Mobile-first version"
  setup_info.sh --name "My Project" --preview-type pc --project-type backend "Updated version"

Description:
  Creates or updates docs/project.json with the project name, version description, preview type, and project type.
  The --name parameter is required for the first run when project.json doesn't exist.
  For subsequent runs, --name is optional and will preserve the existing name if omitted.
  The --preview-type parameter controls the preview display type (pc or mobile), defaults to "pc".
  The --project-type parameter specifies the project type (e.g., web, backend, frontend).

EOF
}

# 解析命令行参数
parse_arguments() {
    if [ $# -eq 0 ]; then
        echo "❌ Error: VERSION_DESCRIPTION is required"
        echo ""
        show_help
        exit 1
    fi

    while [[ $# -gt 0 ]]; do
        case $1 in
            --name)
                if [ -z "$2" ] || [[ "$2" == --* ]]; then
                    echo "❌ Error: --name requires a value"
                    echo ""
                    show_help
                    exit 1
                fi
                PROJECT_NAME="$2"
                shift 2
                ;;
            --preview-type)
                if [ -z "$2" ] || [[ "$2" == --* ]]; then
                    echo "❌ Error: --preview-type requires a value"
                    echo ""
                    show_help
                    exit 1
                fi
                # 验证 preview-type 值
                if [ "$2" != "pc" ] && [ "$2" != "mobile" ]; then
                    echo "❌ Error: --preview-type must be 'pc' or 'mobile', got '$2'"
                    echo ""
                    show_help
                    exit 1
                fi
                PREVIEW_TYPE="$2"
                shift 2
                ;;
            --project-type)
                if [ -z "$2" ] || [[ "$2" == --* ]]; then
                    echo "❌ Error: --project-type requires a value"
                    echo ""
                    show_help
                    exit 1
                fi
                PROJECT_TYPE="$2"
                shift 2
                ;;
            --help|-h)
                show_help
                exit 0
                ;;
            *)
                # 最后一个参数应该是 version_description
                if [ -z "$VERSION_DESCRIPTION" ]; then
                    VERSION_DESCRIPTION="$1"
                else
                    echo "❌ Error: Unexpected argument '$1'"
                    echo ""
                    show_help
                    exit 1
                fi
                shift
                ;;
        esac
    done

    # 验证 version_description 必须存在
    if [ -z "$VERSION_DESCRIPTION" ]; then
        echo "❌ Error: VERSION_DESCRIPTION is required"
        echo ""
        show_help
        exit 1
    fi
}

# 读取现有的项目信息
read_existing_project_info() {
    if [ -f "$PROJECT_JSON" ]; then
        # 使用 python 解析 JSON（更可靠）
        if command -v python3 &> /dev/null; then
            EXISTING_NAME=$(python3 -c "import json; print(json.load(open('$PROJECT_JSON')).get('name', ''))" 2>/dev/null || echo "")
            EXISTING_PREVIEW_TYPE=$(python3 -c "import json; print(json.load(open('$PROJECT_JSON')).get('preview_type', 'pc'))" 2>/dev/null || echo "pc")
            EXISTING_PROJECT_TYPE=$(python3 -c "import json; print(json.load(open('$PROJECT_JSON')).get('project_type', ''))" 2>/dev/null || echo "")
        else
            # 备选方案：使用 grep 和 sed
            EXISTING_NAME=$(grep -o '"name"[[:space:]]*:[[:space:]]*"[^"]*"' "$PROJECT_JSON" | sed 's/"name"[[:space:]]*:[[:space:]]*"\([^"]*\)"/\1/' || echo "")
            EXISTING_PREVIEW_TYPE=$(grep -o '"preview_type"[[:space:]]*:[[:space:]]*"[^"]*"' "$PROJECT_JSON" | sed 's/"preview_type"[[:space:]]*:[[:space:]]*"\([^"]*\)"/\1/' || echo "pc")
            EXISTING_PROJECT_TYPE=$(grep -o '"project_type"[[:space:]]*:[[:space:]]*"[^"]*"' "$PROJECT_JSON" | sed 's/"project_type"[[:space:]]*:[[:space:]]*"\([^"]*\)"/\1/' || echo "")
        fi
        
        if [ -z "$PROJECT_NAME" ]; then
            PROJECT_NAME="$EXISTING_NAME"
        fi
        
        # 如果用户没有通过命令行指定 preview_type，使用现有值
        if [ "$PREVIEW_TYPE" = "pc" ] && [ -n "$EXISTING_PREVIEW_TYPE" ]; then
            PREVIEW_TYPE="$EXISTING_PREVIEW_TYPE"
        fi
        
        # 如果用户没有通过命令行指定 project_type，使用现有值
        if [ -z "$PROJECT_TYPE" ] && [ -n "$EXISTING_PROJECT_TYPE" ]; then
            PROJECT_TYPE="$EXISTING_PROJECT_TYPE"
        fi
    fi
}

# 验证参数
validate_arguments() {
    # 如果是第一次运行，必须提供 --name
    if [ ! -f "$PROJECT_JSON" ] && [ -z "$PROJECT_NAME" ]; then
        echo "❌ Error: --name is required for first time setup"
        echo ""
        show_help
        exit 1
    fi

    # 确保有项目名称
    if [ -z "$PROJECT_NAME" ]; then
        echo "❌ Error: Could not determine project name"
        echo ""
        show_help
        exit 1
    fi
}

# 创建或更新 project.json
create_project_json() {
    # 创建 docs 目录（如果不存在）
    mkdir -p "$DOCS_DIR"

    # 生成 JSON 内容
    if [ -n "$PROJECT_TYPE" ]; then
        # 包含 project_type
        cat > "$PROJECT_JSON" << EOF
{
  "project_type": "$PROJECT_TYPE",
  "sub_project_type": "$SUB_PROJECT_TYPE",
  "name": "$PROJECT_NAME",
  "version_description": "$VERSION_DESCRIPTION",
  "preview_type": "$PREVIEW_TYPE"
}
EOF
    else
        # 不包含 project_type（保持向后兼容）
        cat > "$PROJECT_JSON" << EOF
{
  "name": "$PROJECT_NAME",
  "version_description": "$VERSION_DESCRIPTION",
  "preview_type": "$PREVIEW_TYPE"
}
EOF
    fi

    echo "✅ Successfully created/updated $PROJECT_JSON"
    echo ""
    echo "Project Information:"
    echo "  Name: $PROJECT_NAME"
    echo "  Version Description: $VERSION_DESCRIPTION"
    echo "  Preview Type: $PREVIEW_TYPE"
    if [ -n "$PROJECT_TYPE" ]; then
        echo "  Project Type: $PROJECT_TYPE"
    fi
}

# ============================================================================
# 主执行流程
# ============================================================================

main() {
    parse_arguments "$@"
    read_existing_project_info
    validate_arguments
    create_project_json
}

main "$@"
