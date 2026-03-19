import { Layout } from '@/components/layout';

const Terms = () => {
  return (
    <Layout>
      <div className="min-h-[calc(100vh-110px)] py-8 sm:py-12">
        <div className="mx-auto w-full max-w-3xl px-4 sm:px-6">
          <h1 className="text-2xl sm:text-3xl font-semibold text-foreground tracking-tight mb-6">
            用户协议
          </h1>
          
          <div className="prose prose-sm prose-gray dark:prose-invert max-w-none">
            <p className="text-muted-foreground mb-6">
              最后更新日期：2026年1月
            </p>

            <section className="mb-8">
              <h2 className="text-lg font-medium text-foreground mb-3">1. 服务说明</h2>
              <p className="text-sm text-muted-foreground leading-relaxed">
                ImageGen 是一款基于人工智能的文本生成图片服务。您可以通过输入文字描述，
                使用我们的 AI 技术生成相应的图片内容。使用本服务即表示您同意本用户协议的所有条款。
              </p>
            </section>

            <section className="mb-8">
              <h2 className="text-lg font-medium text-foreground mb-3">2. 账户注册</h2>
              <p className="text-sm text-muted-foreground leading-relaxed mb-3">
                使用本服务的部分功能需要注册账户。您同意：
              </p>
              <ul className="text-sm text-muted-foreground space-y-2 list-disc list-inside">
                <li>提供真实、准确、完整的注册信息</li>
                <li>妥善保管您的账户和密码</li>
                <li>对您账户下的所有活动负责</li>
                <li>发现账户被盗用时立即通知我们</li>
              </ul>
            </section>

            <section className="mb-8">
              <h2 className="text-lg font-medium text-foreground mb-3">3. 使用规范</h2>
              <p className="text-sm text-muted-foreground leading-relaxed mb-3">
                您同意在使用本服务时不会：
              </p>
              <ul className="text-sm text-muted-foreground space-y-2 list-disc list-inside">
                <li>生成违法、淫秽、暴力、歧视性或其他违反公序良俗的内容</li>
                <li>侵犯他人的知识产权、肖像权或其他合法权益</li>
                <li>生成虚假信息或用于欺诈目的的内容</li>
                <li>尝试破坏、干扰或规避服务的安全措施</li>
                <li>将服务用于任何非法目的</li>
              </ul>
            </section>

            <section className="mb-8">
              <h2 className="text-lg font-medium text-foreground mb-3">4. 知识产权</h2>
              <p className="text-sm text-muted-foreground leading-relaxed mb-3">
                关于生成内容的知识产权：
              </p>
              <ul className="text-sm text-muted-foreground space-y-2 list-disc list-inside">
                <li>您通过本服务生成的图片，您拥有使用权</li>
                <li>我们保留对服务本身（包括算法、界面设计等）的所有权利</li>
                <li>您不得将生成的内容用于侵犯他人权益的用途</li>
              </ul>
            </section>

            <section className="mb-8">
              <h2 className="text-lg font-medium text-foreground mb-3">5. 服务变更与终止</h2>
              <p className="text-sm text-muted-foreground leading-relaxed">
                我们保留随时修改、暂停或终止服务的权利，恕不另行通知。
                如果您违反本协议的任何条款，我们有权立即终止您的账户和服务使用权限。
              </p>
            </section>

            <section className="mb-8">
              <h2 className="text-lg font-medium text-foreground mb-3">6. 免责声明</h2>
              <p className="text-sm text-muted-foreground leading-relaxed mb-3">
                本服务按"现状"提供，我们不对以下情况承担责任：
              </p>
              <ul className="text-sm text-muted-foreground space-y-2 list-disc list-inside">
                <li>服务的中断、延迟或错误</li>
                <li>生成内容的准确性、完整性或适用性</li>
                <li>因使用本服务而导致的任何直接或间接损失</li>
                <li>第三方对生成内容的使用或滥用</li>
              </ul>
            </section>

            <section className="mb-8">
              <h2 className="text-lg font-medium text-foreground mb-3">7. 协议修改</h2>
              <p className="text-sm text-muted-foreground leading-relaxed">
                我们可能会不时更新本用户协议。更新后的协议将在本页面发布，
                继续使用服务即表示您接受更新后的协议条款。
              </p>
            </section>

            <section className="mb-8">
              <h2 className="text-lg font-medium text-foreground mb-3">8. 联系方式</h2>
              <p className="text-sm text-muted-foreground leading-relaxed">
                如果您对本用户协议有任何疑问，请联系我们：
                <br />
                电子邮件：support@imagegen.example.com
              </p>
            </section>
          </div>
        </div>
      </div>
    </Layout>
  );
};

export default Terms;
