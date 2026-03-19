<!-- Template: 科技风格-变体8 (Content #1588) - 数据库设计 -->
<div class="w-[1440px] h-[810px] relative overflow-hidden" style="background-color: #0F172A;">
    <!-- 底部线 -->
    <div class="absolute bottom-0 left-0 right-0 h-[3px]" style="background-color: #06B6D4;"></div>
    <div class="absolute inset-0 flex flex-col px-14 py-10">
        <!-- 标题 -->
        <div class="flex items-center gap-4 mb-5">
            <div class="px-3 py-1" style="background-color: #06B6D4;">
                <span class="font-mono text-[14px] font-bold" style="color: #0F172A;">DB</span>
            </div>
            <div>
                <h1 class="text-[38px] font-bold text-white">数据库架构设计</h1>
                <p class="text-[13px] font-mono" style="color: #475569;">DATABASE SCHEMA DESIGN</p>
            </div>
        </div>
        <!-- 表结构 -->
        <div class="flex-1 flex gap-5">
            <!-- 左侧：3个表 -->
            <div class="flex-1 flex flex-col gap-4">
                <!-- Users 表 -->
                <div class="p-4" style="background-color: #1E293B;">
                    <div class="flex items-center gap-2 mb-3">
                        <div class="w-3 h-3" style="background-color: #06B6D4;"></div>
                        <span class="font-mono text-[14px] font-bold" style="color: #06B6D4;">users</span>
                    </div>
                    <div class="space-y-1 font-mono text-[12px]">
                        <div class="flex gap-4 p-1" style="background-color: #0F172A;"><span class="w-24" style="color: #F59E0B;">PK</span><span class="w-28" style="color: #94A3B8;">id</span><span style="color: #64748B;">BIGINT AUTO_INCREMENT</span></div>
                        <div class="flex gap-4 p-1"><span class="w-24" style="color: #64748B;">NOT NULL</span><span class="w-28" style="color: #94A3B8;">username</span><span style="color: #64748B;">VARCHAR(64) UNIQUE</span></div>
                        <div class="flex gap-4 p-1" style="background-color: #0F172A;"><span class="w-24" style="color: #64748B;">NOT NULL</span><span class="w-28" style="color: #94A3B8;">email</span><span style="color: #64748B;">VARCHAR(128) UNIQUE</span></div>
                        <div class="flex gap-4 p-1"><span class="w-24" style="color: #64748B;">NOT NULL</span><span class="w-28" style="color: #94A3B8;">password_hash</span><span style="color: #64748B;">VARCHAR(256)</span></div>
                        <div class="flex gap-4 p-1" style="background-color: #0F172A;"><span class="w-24" style="color: #64748B;">DEFAULT</span><span class="w-28" style="color: #94A3B8;">created_at</span><span style="color: #64748B;">TIMESTAMP</span></div>
                    </div>
                </div>
                <!-- Orders 表 -->
                <div class="p-4" style="background-color: #1E293B;">
                    <div class="flex items-center gap-2 mb-3">
                        <div class="w-3 h-3" style="background-color: #3B82F6;"></div>
                        <span class="font-mono text-[14px] font-bold" style="color: #3B82F6;">orders</span>
                    </div>
                    <div class="space-y-1 font-mono text-[12px]">
                        <div class="flex gap-4 p-1" style="background-color: #0F172A;"><span class="w-24" style="color: #F59E0B;">PK</span><span class="w-28" style="color: #94A3B8;">id</span><span style="color: #64748B;">BIGINT AUTO_INCREMENT</span></div>
                        <div class="flex gap-4 p-1"><span class="w-24" style="color: #EF4444;">FK</span><span class="w-28" style="color: #94A3B8;">user_id</span><span style="color: #64748B;">BIGINT -> users.id</span></div>
                        <div class="flex gap-4 p-1" style="background-color: #0F172A;"><span class="w-24" style="color: #64748B;">NOT NULL</span><span class="w-28" style="color: #94A3B8;">total_amount</span><span style="color: #64748B;">DECIMAL(10,2)</span></div>
                        <div class="flex gap-4 p-1"><span class="w-24" style="color: #64748B;">ENUM</span><span class="w-28" style="color: #94A3B8;">status</span><span style="color: #64748B;">pending|paid|shipped</span></div>
                    </div>
                </div>
                <!-- Products 表 -->
                <div class="flex-1 p-4" style="background-color: #1E293B;">
                    <div class="flex items-center gap-2 mb-3">
                        <div class="w-3 h-3" style="background-color: #8B5CF6;"></div>
                        <span class="font-mono text-[14px] font-bold" style="color: #8B5CF6;">products</span>
                    </div>
                    <div class="space-y-1 font-mono text-[12px]">
                        <div class="flex gap-4 p-1" style="background-color: #0F172A;"><span class="w-24" style="color: #F59E0B;">PK</span><span class="w-28" style="color: #94A3B8;">id</span><span style="color: #64748B;">BIGINT AUTO_INCREMENT</span></div>
                        <div class="flex gap-4 p-1"><span class="w-24" style="color: #64748B;">NOT NULL</span><span class="w-28" style="color: #94A3B8;">name</span><span style="color: #64748B;">VARCHAR(128)</span></div>
                        <div class="flex gap-4 p-1" style="background-color: #0F172A;"><span class="w-24" style="color: #64748B;">NOT NULL</span><span class="w-28" style="color: #94A3B8;">price</span><span style="color: #64748B;">DECIMAL(10,2)</span></div>
                        <div class="flex gap-4 p-1"><span class="w-24" style="color: #64748B;">DEFAULT 0</span><span class="w-28" style="color: #94A3B8;">stock</span><span style="color: #64748B;">INT UNSIGNED</span></div>
                    </div>
                </div>
            </div>
            <!-- 右侧：索引 + 规范 -->
            <div class="w-[400px] flex-shrink-0 flex flex-col gap-4">
                <div class="p-5" style="background-color: #1E293B; border-left: 3px solid #06B6D4;">
                    <p class="text-[16px] font-bold mb-3 text-white">索引策略</p>
                    <div class="space-y-2 font-mono text-[12px]">
                        <div class="p-2" style="background-color: #0F172A;">
                            <p style="color: #06B6D4;">idx_users_email</p>
                            <p style="color: #475569;">UNIQUE INDEX ON users(email)</p>
                        </div>
                        <div class="p-2" style="background-color: #0F172A;">
                            <p style="color: #3B82F6;">idx_orders_user</p>
                            <p style="color: #475569;">INDEX ON orders(user_id, status)</p>
                        </div>
                        <div class="p-2" style="background-color: #0F172A;">
                            <p style="color: #8B5CF6;">idx_products_name</p>
                            <p style="color: #475569;">FULLTEXT INDEX ON products(name)</p>
                        </div>
                    </div>
                </div>
                <div class="p-5" style="background-color: #1E293B;">
                    <p class="text-[16px] font-bold mb-3 text-white">设计规范</p>
                    <div class="space-y-2">
                        <div class="flex gap-2 items-start">
                            <div class="w-2 h-2 mt-1.5 flex-shrink-0" style="background-color: #06B6D4;"></div>
                            <p class="text-[13px]" style="color: #94A3B8;">主键使用 BIGINT 自增，预留扩展空间</p>
                        </div>
                        <div class="flex gap-2 items-start">
                            <div class="w-2 h-2 mt-1.5 flex-shrink-0" style="background-color: #06B6D4;"></div>
                            <p class="text-[13px]" style="color: #94A3B8;">所有表包含 created_at / updated_at</p>
                        </div>
                        <div class="flex gap-2 items-start">
                            <div class="w-2 h-2 mt-1.5 flex-shrink-0" style="background-color: #06B6D4;"></div>
                            <p class="text-[13px]" style="color: #94A3B8;">金额字段统一 DECIMAL(10,2)</p>
                        </div>
                        <div class="flex gap-2 items-start">
                            <div class="w-2 h-2 mt-1.5 flex-shrink-0" style="background-color: #06B6D4;"></div>
                            <p class="text-[13px]" style="color: #94A3B8;">软删除使用 deleted_at 字段</p>
                        </div>
                    </div>
                </div>
                <div class="p-3 font-mono" style="background-color: #1E293B;">
                    <p class="text-[12px]" style="color: #475569;">// MySQL 8.0 InnoDB | UTF8MB4</p>
                </div>
            </div>
        </div>
    </div>
</div>