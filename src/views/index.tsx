import { Page } from "../components/page"

export const Index = () => {
  return Page('Home', `
    <p style="text-align:center;">
      Choose the method you would like to use to login or register.
    </p>
    <div class="tiles">
      <div class="tile">
        <a href="#" class="tile-cover" data-cancel style="background-color:#666;">
          <i>Authenticate by email</i>
          <h2>Passwordless</h2>
        </a>
        <div class="tile-content">
          <a href="" data-close>← Back</a>
          <h2>Passwordless Email Authentication</h2>
          <form action="#">
            <label for="pless-email">Email address:</label>
            <input id="pless-email" type="email" placeholder="example@example.com" autofocus>
            <button type="submit">Login / Register</button>
          </form>
        </div>
      </div>
    </div>`);
}