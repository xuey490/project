<!-- Template: 科技风-战术HUD (Transition #562) -->
            <div class="w-[1440px] h-[810px] shadow-2xl relative overflow-hidden bg-black">
                <div class="w-[1350px] h-[720px] absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2">

                    <!-- 扫描线背景 -->
                    <svg class="absolute inset-0 w-full h-full opacity-[0.03]" xmlns="http://www.w3.org/2000/svg">
                        <pattern id="t562-scan" width="1350" height="4" patternUnits="userSpaceOnUse">
                            <line x1="0" y1="2" x2="1350" y2="2" stroke="#22c55e" stroke-width="0.5"/>
                        </pattern>
                        <rect width="100%" height="100%" fill="url(#t562-scan)"/>
                    </svg>

                    <!-- 中央雷达/瞄准系统 -->
                    <svg class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[480px] h-[480px]" viewBox="0 0 480 480" xmlns="http://www.w3.org/2000/svg">
                        <!-- 外圈 -->
                        <circle cx="240" cy="240" r="230" fill="none" stroke="#166534" stroke-width="1" opacity="0.3"/>
                        <!-- 刻度线 - 外圈 -->
                        <line x1="240" y1="10" x2="240" y2="30" stroke="#22c55e" stroke-width="1" opacity="0.4"/>
                        <line x1="240" y1="450" x2="240" y2="470" stroke="#22c55e" stroke-width="1" opacity="0.4"/>
                        <line x1="10" y1="240" x2="30" y2="240" stroke="#22c55e" stroke-width="1" opacity="0.4"/>
                        <line x1="450" y1="240" x2="470" y2="240" stroke="#22c55e" stroke-width="1" opacity="0.4"/>
                        <!-- 45度刻度 -->
                        <line x1="77" y1="77" x2="91" y2="91" stroke="#22c55e" stroke-width="0.8" opacity="0.3"/>
                        <line x1="389" y1="77" x2="403" y2="91" stroke="#22c55e" stroke-width="0.8" opacity="0.3" transform="rotate(90 396 84)"/>
                        <line x1="77" y1="389" x2="91" y2="403" stroke="#22c55e" stroke-width="0.8" opacity="0.3" transform="rotate(-90 84 396)"/>
                        <line x1="389" y1="389" x2="403" y2="403" stroke="#22c55e" stroke-width="0.8" opacity="0.3"/>

                        <!-- 中圈 -->
                        <circle cx="240" cy="240" r="160" fill="none" stroke="#166534" stroke-width="0.8" opacity="0.25" stroke-dasharray="6 4"/>

                        <!-- 内圈 -->
                        <circle cx="240" cy="240" r="90" fill="none" stroke="#166534" stroke-width="0.6" opacity="0.2"/>

                        <!-- 十字准线 -->
                        <line x1="240" y1="80" x2="240" y2="180" stroke="#22c55e" stroke-width="0.6" opacity="0.3"/>
                        <line x1="240" y1="300" x2="240" y2="400" stroke="#22c55e" stroke-width="0.6" opacity="0.3"/>
                        <line x1="80" y1="240" x2="180" y2="240" stroke="#22c55e" stroke-width="0.6" opacity="0.3"/>
                        <line x1="300" y1="240" x2="400" y2="240" stroke="#22c55e" stroke-width="0.6" opacity="0.3"/>

                        <!-- 中心菱形瞄准框 -->
                        <rect x="224" y="224" width="32" height="32" fill="none" stroke="#22c55e" stroke-width="1.5" opacity="0.5" transform="rotate(45 240 240)"/>

                        <!-- 目标标记点 -->
                        <circle cx="310" cy="180" r="3" fill="none" stroke="#22c55e" stroke-width="1" opacity="0.5"/>
                        <circle cx="310" cy="180" r="1" fill="#22c55e" opacity="0.6"/>
                        <circle cx="170" cy="320" r="2.5" fill="none" stroke="#22c55e" stroke-width="0.8" opacity="0.4"/>
                        <circle cx="170" cy="320" r="0.8" fill="#22c55e" opacity="0.5"/>
                        <circle cx="340" cy="310" r="2" fill="none" stroke="#22c55e" stroke-width="0.8" opacity="0.35"/>
                        <circle cx="340" cy="310" r="0.7" fill="#22c55e" opacity="0.45"/>
                    </svg>

                    <!-- 四角HUD框架 -->
                    <!-- 左上 -->
                    <div class="absolute top-[20px] left-[20px]">
                        <div class="w-[100px] h-[60px] border-l-2 border-t-2 border-green-600 opacity-50"></div>
                        <div class="absolute top-[8px] left-[10px] text-[9px] font-mono text-green-700 tracking-wider">SYS_01</div>
                        <div class="absolute top-[22px] left-[10px] text-[10px] font-mono text-green-500">ACTIVE</div>
                    </div>
                    <!-- 右上 -->
                    <div class="absolute top-[20px] right-[20px]">
                        <div class="w-[100px] h-[60px] border-r-2 border-t-2 border-green-600 opacity-50 ml-auto"></div>
                        <div class="absolute top-[8px] right-[10px] text-[9px] font-mono text-green-700 tracking-wider text-right">TGT_LOCK</div>
                        <div class="absolute top-[22px] right-[10px] text-[10px] font-mono text-green-500 text-right">CONFIRMED</div>
                    </div>
                    <!-- 左下 -->
                    <div class="absolute bottom-[20px] left-[20px]">
                        <div class="w-[100px] h-[60px] border-l-2 border-b-2 border-green-600 opacity-50"></div>
                        <div class="absolute bottom-[22px] left-[10px] text-[9px] font-mono text-green-700 tracking-wider">GRID_REF</div>
                        <div class="absolute bottom-[8px] left-[10px] text-[10px] font-mono text-green-500">24.7°N</div>
                    </div>
                    <!-- 右下 -->
                    <div class="absolute bottom-[20px] right-[20px]">
                        <div class="w-[100px] h-[60px] border-r-2 border-b-2 border-green-600 opacity-50 ml-auto"></div>
                        <div class="absolute bottom-[22px] right-[10px] text-[9px] font-mono text-green-700 tracking-wider text-right">BEARING</div>
                        <div class="absolute bottom-[8px] right-[10px] text-[10px] font-mono text-green-500 text-right">117.3°E</div>
                    </div>

                    <!-- 左侧仪表数据条 -->
                    <div class="absolute top-[120px] left-[24px] flex flex-col gap-[6px]">
                        <div class="flex items-center gap-2">
                            <div class="w-[40px] h-[3px] bg-green-500 opacity-60"></div>
                            <span class="text-[8px] font-mono text-green-700">PWR</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <div class="w-[32px] h-[3px] bg-green-500 opacity-50"></div>
                            <span class="text-[8px] font-mono text-green-700">SIG</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <div class="w-[48px] h-[3px] bg-green-500 opacity-70"></div>
                            <span class="text-[8px] font-mono text-green-700">RNG</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <div class="w-[28px] h-[3px] bg-green-600 opacity-40"></div>
                            <span class="text-[8px] font-mono text-green-700">THR</span>
                        </div>
                    </div>

                    <!-- 右侧仪表数据条 -->
                    <div class="absolute top-[120px] right-[24px] flex flex-col gap-[6px] items-end">
                        <div class="flex items-center gap-2">
                            <span class="text-[8px] font-mono text-green-700">ALT</span>
                            <div class="w-[36px] h-[3px] bg-green-500 opacity-55"></div>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="text-[8px] font-mono text-green-700">SPD</span>
                            <div class="w-[44px] h-[3px] bg-green-500 opacity-65"></div>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="text-[8px] font-mono text-green-700">HDG</span>
                            <div class="w-[30px] h-[3px] bg-green-500 opacity-45"></div>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="text-[8px] font-mono text-green-700">FRQ</span>
                            <div class="w-[50px] h-[3px] bg-green-500 opacity-75"></div>
                        </div>
                    </div>

                    <!-- 中央文字内容 -->
                    <div class="absolute inset-0 flex items-center justify-center z-10">
                        <div class="text-center">
                            <!-- 系统标签 -->
                            <div class="text-[10px] font-mono text-green-700 tracking-[0.4em] mb-4">TACTICAL_DISPLAY</div>

                            <!-- 编号 -->
                            <div class="text-[72px] font-mono font-bold text-green-400 leading-none mb-4">01</div>

                            <!-- 分隔 -->
                            <div class="flex items-center justify-center gap-2 mb-4">
                                <div class="w-1 h-1 bg-green-500"></div>
                                <div class="w-[80px] h-px bg-green-600"></div>
                                <div class="w-2 h-2 border border-green-500 transform rotate-45"></div>
                                <div class="w-[80px] h-px bg-green-600"></div>
                                <div class="w-1 h-1 bg-green-500"></div>
                            </div>

                            <!-- 标题 -->
                            <h1 class="text-[34px] font-bold text-green-100 mb-3 tracking-wider">{{title}}</h1>
                            <p class="text-sm text-green-500 tracking-[0.2em] font-mono">{{subtitle}}</p>

                            <!-- 底部状态 -->
                            <div class="flex items-center justify-center gap-3 mt-6">
                                <div class="w-2 h-2 rounded-full bg-green-500 opacity-80"></div>
                                <span class="text-[9px] font-mono text-green-600 tracking-widest">SYSTEM ONLINE</span>
                                <div class="w-2 h-2 rounded-full bg-green-500 opacity-80"></div>
                            </div>
                        </div>
                    </div>

                    <!-- 顶部中央标签 -->
                    <div class="absolute top-[10px] left-1/2 -translate-x-1/2 flex items-center gap-3">
                        <div class="w-[60px] h-px bg-green-700"></div>
                        <span class="text-[9px] font-mono text-green-600 tracking-[0.3em]">HUD v4.0</span>
                        <div class="w-[60px] h-px bg-green-700"></div>
                    </div>

                    <!-- 底部中央标签 -->
                    <div class="absolute bottom-[10px] left-1/2 -translate-x-1/2 flex items-center gap-3">
                        <div class="w-[60px] h-px bg-green-700"></div>
                        <span class="text-[9px] font-mono text-green-600 tracking-[0.3em]">SECTOR 7G</span>
                        <div class="w-[60px] h-px bg-green-700"></div>
                    </div>

                </div>
            </div>
