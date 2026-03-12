<!-- Template: 科技风-数据矩阵 (Transition #560) -->
            <div class="w-[1440px] h-[810px] shadow-2xl relative overflow-hidden bg-slate-950">
                <div class="w-[1350px] h-[720px] absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2">

                    <!-- 点阵背景 -->
                    <svg class="absolute inset-0 w-full h-full opacity-[0.04]" xmlns="http://www.w3.org/2000/svg">
                        <pattern id="t560-dots" width="20" height="20" patternUnits="userSpaceOnUse">
                            <circle cx="10" cy="10" r="0.8" fill="#3b82f6"/>
                        </pattern>
                        <rect width="100%" height="100%" fill="url(#t560-dots)"/>
                    </svg>

                    <!-- 左侧二进制数据列 -->
                    <div class="absolute top-[40px] left-[30px] text-[10px] font-mono text-blue-800 leading-[18px] opacity-40 select-none">
                        01001101<br/>10110010<br/>00101101<br/>11010010<br/>01101001<br/>10010110<br/>01001011<br/>11100101<br/>00110110<br/>10101001<br/>01010110<br/>11001010<br/>00101101<br/>10110100<br/>01001011<br/>11010010<br/>00110101<br/>10101010<br/>01100110<br/>11001001<br/>00101011<br/>10010110<br/>01101001<br/>10110010<br/>01001101<br/>11100100<br/>00110110<br/>10101001<br/>01010110<br/>11001010<br/>00101101<br/>10110100<br/>01001011<br/>11010010<br/>00110101<br/>10101010
                    </div>

                    <!-- 右侧二进制数据列 -->
                    <div class="absolute top-[40px] right-[30px] text-[10px] font-mono text-blue-800 leading-[18px] opacity-40 select-none text-right">
                        11010010<br/>00101101<br/>10110010<br/>01001101<br/>10010110<br/>01101001<br/>11100101<br/>01001011<br/>10101001<br/>00110110<br/>01010110<br/>11001010<br/>10110100<br/>00101101<br/>11010010<br/>01001011<br/>10101010<br/>00110101<br/>01100110<br/>11001001<br/>10010110<br/>00101011<br/>10110010<br/>01101001<br/>11100100<br/>01001101<br/>10101001<br/>00110110<br/>11001010<br/>01010110<br/>10110100<br/>00101101<br/>11010010<br/>01001011<br/>10101010<br/>00110101
                    </div>

                    <!-- 第二列数据 -->
                    <div class="absolute top-[60px] left-[110px] text-[10px] font-mono text-blue-900 leading-[18px] opacity-25 select-none">
                        10010110<br/>01101001<br/>10110010<br/>01001011<br/>11010010<br/>00110101<br/>10101001<br/>01010110<br/>11001010<br/>00101101<br/>10110100<br/>01001011<br/>11100101<br/>00110110<br/>10101010<br/>01100110<br/>11001001<br/>00101011<br/>10010110<br/>01101001<br/>11010010<br/>01001101<br/>10110010<br/>00101101<br/>01001011<br/>11100100<br/>10101001<br/>00110110<br/>01010110<br/>11001010<br/>00101101<br/>10110100<br/>01001011<br/>11010010<br/>00110101
                    </div>
                    <div class="absolute top-[60px] right-[110px] text-[10px] font-mono text-blue-900 leading-[18px] opacity-25 select-none text-right">
                        01001011<br/>11010010<br/>00110101<br/>10010110<br/>01101001<br/>10110010<br/>10101001<br/>01010110<br/>11001010<br/>00101101<br/>10110100<br/>01001011<br/>11100101<br/>00110110<br/>10101010<br/>01100110<br/>11001001<br/>00101011<br/>01001101<br/>10110010<br/>00101101<br/>10010110<br/>01101001<br/>11010010<br/>11100100<br/>10101001<br/>00110110<br/>01010110<br/>11001010<br/>00101101<br/>10110100<br/>01001011<br/>11010010<br/>00110101<br/>10101010
                    </div>

                    <!-- 水平扫描线 -->
                    <div class="absolute top-[280px] left-[180px] right-[180px] h-px bg-blue-500 opacity-15"></div>
                    <div class="absolute top-[440px] left-[180px] right-[180px] h-px bg-blue-500 opacity-15"></div>

                    <!-- 中央数据窗口 -->
                    <div class="absolute inset-0 flex items-center justify-center">
                        <div class="relative">
                            <!-- 外框 -->
                            <div class="w-[520px] h-[360px] border border-blue-700 relative" style="box-shadow: 0 0 40px rgba(59,130,246,0.06);">
                                <!-- 四角标记 -->
                                <div class="absolute -top-1 -left-1 w-4 h-4 border-t-2 border-l-2 border-blue-500"></div>
                                <div class="absolute -top-1 -right-1 w-4 h-4 border-t-2 border-r-2 border-blue-500"></div>
                                <div class="absolute -bottom-1 -left-1 w-4 h-4 border-b-2 border-l-2 border-blue-500"></div>
                                <div class="absolute -bottom-1 -right-1 w-4 h-4 border-b-2 border-r-2 border-blue-500"></div>

                                <!-- 顶部栏 -->
                                <div class="absolute top-0 left-0 right-0 h-[36px] border-b border-blue-800 flex items-center px-4 justify-between">
                                    <div class="flex items-center gap-2">
                                        <div class="w-2 h-2 rounded-full bg-blue-500"></div>
                                        <span class="text-[10px] font-mono text-blue-600 tracking-widest">DATA_STREAM</span>
                                    </div>
                                    <div class="flex gap-1">
                                        <div class="w-1 h-3 bg-blue-700"></div>
                                        <div class="w-1 h-4 bg-blue-600"></div>
                                        <div class="w-1 h-5 bg-blue-500"></div>
                                        <div class="w-1 h-3 bg-blue-700"></div>
                                        <div class="w-1 h-4 bg-blue-600"></div>
                                    </div>
                                </div>

                                <!-- 主内容 -->
                                <div class="absolute top-[36px] left-0 right-0 bottom-[32px] flex items-center justify-center">
                                    <div class="text-center">
                                        <!-- 编号 -->
                                        <div class="flex items-center justify-center gap-6 mb-6">
                                            <div class="w-[60px] h-px bg-blue-600 opacity-50"></div>
                                            <div class="text-[80px] font-mono font-bold text-blue-400 leading-none">01</div>
                                            <div class="w-[60px] h-px bg-blue-600 opacity-50"></div>
                                        </div>

                                        <!-- 分隔 -->
                                        <div class="flex items-center justify-center gap-2 mb-5">
                                            <div class="w-2 h-2 bg-blue-500 opacity-60"></div>
                                            <div class="w-[120px] h-px bg-blue-600"></div>
                                            <div class="w-2 h-2 bg-blue-500 opacity-60"></div>
                                        </div>

                                        <!-- 标题 -->
                                        <h1 class="text-[34px] font-bold text-white mb-3 tracking-wider">{{title}}</h1>
                                        <p class="text-sm text-blue-400 tracking-[0.2em]">{{subtitle}}</p>
                                    </div>
                                </div>

                                <!-- 底部栏 -->
                                <div class="absolute bottom-0 left-0 right-0 h-[32px] border-t border-blue-800 flex items-center px-4 justify-between">
                                    <span class="text-[9px] font-mono text-blue-700 tracking-wider">MATRIX_DECODE v3.1</span>
                                    <div class="flex items-center gap-3">
                                        <span class="text-[9px] font-mono text-blue-700">BITRATE: 1.21 Gbps</span>
                                        <div class="flex gap-0.5">
                                            <div class="w-1.5 h-1.5 bg-blue-500 opacity-80"></div>
                                            <div class="w-1.5 h-1.5 bg-blue-500 opacity-60"></div>
                                            <div class="w-1.5 h-1.5 bg-blue-600 opacity-40"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- 顶部状态 -->
                    <div class="absolute top-3 left-5 text-[9px] font-mono text-blue-800 tracking-widest">DECRYPT_MODE</div>
                    <div class="absolute top-3 right-5 text-[9px] font-mono text-blue-800 tracking-widest">STREAM_ACTIVE</div>

                    <!-- 底部状态 -->
                    <div class="absolute bottom-3 left-5 text-[9px] font-mono text-blue-800 tracking-wider">BUFFER: 98.7%</div>
                    <div class="absolute bottom-3 right-5 text-[9px] font-mono text-blue-800 tracking-wider">PACKETS: 1,024,768</div>

                </div>
            </div>
