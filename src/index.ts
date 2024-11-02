import { Resend } from "resend";
import { Index } from "./views/index";

export default {
  async fetch(request, env, ctx): Promise<Response> {
    /* - Example of sending an email via Resend
		const resend = new Resend(env.RESEND_KEY);

		const magic = crypto.randomBytes(size).toString("base64url");

    const { data, error } = await resend.emails.send({
      from: "passport@yiays.com",
      to: "yesiateyoursheep@gmail.com",
      subject: "Hello World",
      html: "<p>Hello from Workers</p>",
			text: "Hello from Workers"
    });

    return Response.json({ data, error });
		*/

		return new Response(Index(), {headers: {'content-type': 'text/html;charset=UTF-8'}});
  },
} satisfies ExportedHandler<Env>;