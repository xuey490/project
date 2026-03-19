<!-- Template: 科技风格-变体10 (Content #1590) - 版本发布说明 -->
<div class="w-[1440px] h-[810px] relative overflow-hidden" style="background-color: #0F172A;">
    <!-- 顶部线 -->
    <div class="absolute top-0 left-0 right-0 h-[3px]" style="background-color: #06B6D4;"></div>
    <div class="absolute inset-0 flex">
        <!-- 左侧版本信息 -->
        <div class="w-[400px] flex-shrink-0 flex flex-col px-12 py-10" style="background-color: #1E293B;">
            <div class="mb-6">
                <p class="text-[13px] font-mono tracking-widest mb-1" style="color: #475569;">RELEASE NOTES</p>
                <h1 class="text-[40px] font-bold text-white mb-2">v4.0.0</h1>
                <div class="w-10 h-[2px] mb-3" style="background-color: #06B6D4;"></div>
                <p class="text-[14px] leading-relaxed" style="color: #94A3B8;">本次大版本更新带来全新的架构设计、性能优化和功能增强。</p>
            </div>
            <!-- 版本统计 -->
            <div class="space-y-4 mb-6">
                <div>
                    <div class="flex justify-between mb-1"><span class="text-[12px] font-mono" style="color: #475569;">Commits</span><span class="font-mono text-[16px] font-bold text-white">482</span></div>
                </div>
                <div>
                    <div class="flex justify-between mb-1"><span class="text-[12px] font-mono" style="color: #475569;">Files Changed</span><span class="font-mono text-[16px] font-bold text-white">156</span></div>
                </div>
                <div>
                    <div class="flex justify-between mb-1"><span class="text-[12px] font-mono" style="color: #475569;">Contributors</span><span class="font-mono text-[16px] font-bold text-white">24</span></div>
                </div>
                <div>
                    <div class="flex justify-between mb-1"><span class="text-[12px] font-mono" style="color: #475569;">Test Coverage</span><span class="font-mono text-[16px] font-bold" style="color: #10B981;">92%</span></div>
                </div>
            </div>
            <!-- 发布信息 -->
            <div class="mt-auto p-4" style="background-color: #0F172A;">
                <p class="text-[12px] font-mono" style="color: #475569;">Released: 2024-12-01</p>
                <p class="text-[12px] font-mono" style="color: #475569;">License: MIT</p>
            </div>
        </div>
        <!-- 右侧变更日志 -->
        <div class="flex-1 flex flex-col px-10 py-10">
            <h2 class="text-[22px] font-bold text-white mb-5">Changelog</h2>
            <div class="flex-1 flex flex-col gap-4 overflow-hidden">
                <!-- New Features -->
                <div class="p-4" style="background-color: #1E293B; border-left: 3px solid #10B981;">
                    <div class="flex items-center gap-2 mb-2">
                        <span class="px-2 py-0.5 text-[11px] font-bold font-mono" style="background-color: #10B981; color: #020617;">NEW</span>
                        <span class="text-[16px] font-bold text-white">新功能</span>
                    </div>
                    <div class="space-y-1 font-mono text-[13px]" style="color: #94A3B8;">
                        <p>+ 支持实时协作编辑（多人同时在线）</p>
                        <p>+ 新增 AI 智能助手集成</p>
                        <p>+ 暗色模式全面支持</p>
                        <p>+ 插件市场 v2.0 上线</p>
                    </div>
                </div>
                <!-- Improvements -->
                <div class="p-4" style="background-color: #1E293B; border-left: 3px solid #3B82F6;">
                    <div class="flex items-center gap-2 mb-2">
                        <span class="px-2 py-0.5 text-[11px] font-bold font-mono" style="background-color: #3B82F6; color: white;">IMPROVED</span>
                        <span class="text-[16px] font-bold text-white">性能优化</span>
                    </div>
                    <div class="space-y-1 font-mono text-[13px]" style="color: #94A3B8;">
                        <p>* 页面加载速度提升 40%（Code Splitting）</p>
                        <p>* 数据库查询优化，响应时间降低 60%</p>
                        <p>* 内存占用减少 25%（GC 调优）</p>
                    </div>
                </div>
                <!-- Bug Fixes -->
                <div class="p-4" style="background-color: #1E293B; border-left: 3px solid #EF4444;">
                    <div class="flex items-center gap-2 mb-2">
                        <span class="px-2 py-0.5 text-[11px] font-bold font-mono" style="background-color: #EF4444; color: white;">FIXED</span>
                        <span class="text-[16px] font-bold text-white">问题修复</span>
                    </div>
                    <div class="space-y-1 font-mono text-[13px]" style="color: #94A3B8;">
                        <p>- 修复并发场景下的数据竞争问题 #1842</p>
                        <p>- 修复大文件上传中断后无法续传 #1756</p>
                        <p>- 修复时区转换导致的日期显示错误 #1698</p>
                    </div>
                </div>
                <!-- Breaking Changes -->
                <div class="p-4" style="background-color: #1E293B; border-left: 3px solid #F59E0B;">
                    <div class="flex items-center gap-2 mb-2">
                        <span class="px-2 py-0.5 text-[11px] font-bold font-mono" style="background-color: #F59E0B; color: #451A03;">BREAKING</span>
                        <span class="text-[16px] font-bold text-white">不兼容变更</span>
                    </div>
                    <div class="space-y-1 font-mono text-[13px]" style="color: #94A3B8;">
                        <p>! Node.js 最低版本要求提升至 20 LTS</p>
                        <p>! API v1 正式废弃，请迁移至 v2</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>