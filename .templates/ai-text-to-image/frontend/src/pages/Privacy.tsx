import { Layout } from '@/components/layout';

const Privacy = () => {
  return (
    <Layout>
      <div className="min-h-[calc(100vh-110px)] py-8 sm:py-12">
        <div className="mx-auto w-full max-w-3xl px-4 sm:px-6">
          <h1 className="text-2xl sm:text-3xl font-semibold text-foreground tracking-tight mb-6">
            隐私政策
          </h1>
          
          <div className="prose prose-sm prose-gray dark:prose-invert max-w-none">
            <p className="text-muted-foreground mb-6">
              最后更新日期：2026年1月
            </p>

            <section className="mb-8">
              <h2 className="text-lg font-medium text-foreground mb-3">1. 信息收集</h2>
              <p className="text-sm text-muted-foreground leading-relaxed mb-3">
                我们收集以下类型的信息：
              </p>
              <ul className="text-sm text-muted-foreground space-y-2 list-disc list-inside">
                <li>账户信息：当您注册账户时，我们会收集您的电子邮件地址和用户名</li>
                <li>使用数据：我们会记录您使用服务的方式，包括生成的图片、输入的描述词等</li>
                <li>设备信息：我们可能收集有关您访问设备的信息，如 IP 地址、浏览器类型等</li>
              </ul>
            </section>

            <section className="mb-8">
              <h2 className="text-lg font-medium text-foreground mb-3">2. 信息使用</h2>
              <p className="text-sm text-muted-foreground leading-relaxed mb-3">
                我们使用收集的信息用于：
              </p>
              <ul className="text-sm text-muted-foreground space-y-2 list-disc list-inside">
                <li>提供、维护和改进我们的服务</li>
                <li>处理您的请求并向您提供客户支持</li>
                <li>发送与服务相关的通知和更新</li>
                <li>监测和分析使用趋势以改进用户体验</li>
              </ul>
            </section>

            <section className="mb-8">
              <h2 className="text-lg font-medium text-foreground mb-3">3. 信息共享</h2>
              <p className="text-sm text-muted-foreground leading-relaxed">
                我们不会出售、交易或以其他方式向外部方转让您的个人信息，除非：
              </p>
              <ul className="text-sm text-muted-foreground space-y-2 list-disc list-inside mt-3">
                <li>获得您的明确同意</li>
                <li>法律要求或政府机关依法要求</li>
                <li>保护我们的权利、财产或安全</li>
              </ul>
            </section>

            <section className="mb-8">
              <h2 className="text-lg font-medium text-foreground mb-3">4. 数据安全</h2>
              <p className="text-sm text-muted-foreground leading-relaxed">
                我们采取适当的技术和组织措施来保护您的个人信息免受未经授权的访问、使用或披露。
                但请注意，没有任何互联网传输或电子存储方法是完全安全的。
              </p>
            </section>

            <section className="mb-8">
              <h2 className="text-lg font-medium text-foreground mb-3">5. Cookie 使用</h2>
              <p className="text-sm text-muted-foreground leading-relaxed">
                我们使用 Cookie 和类似技术来记住您的偏好设置、分析网站流量并改善您的用户体验。
                您可以通过浏览器设置管理 Cookie 偏好。
              </p>
            </section>

            <section className="mb-8">
              <h2 className="text-lg font-medium text-foreground mb-3">6. 您的权利</h2>
              <p className="text-sm text-muted-foreground leading-relaxed mb-3">
                您有权：
              </p>
              <ul className="text-sm text-muted-foreground space-y-2 list-disc list-inside">
                <li>访问和获取您的个人数据副本</li>
                <li>更正不准确的个人数据</li>
                <li>请求删除您的个人数据</li>
                <li>撤回您的同意</li>
              </ul>
            </section>

            <section className="mb-8">
              <h2 className="text-lg font-medium text-foreground mb-3">7. 联系我们</h2>
              <p className="text-sm text-muted-foreground leading-relaxed">
                如果您对本隐私政策有任何疑问，请通过以下方式联系我们：
                <br />
                电子邮件：privacy@imagegen.example.com
              </p>
            </section>
          </div>
        </div>
      </div>
    </Layout>
  );
};

export default Privacy;
