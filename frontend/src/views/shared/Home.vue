<template>
  <PublicHeader />
  <div class="home full-height-header">
    <section class="home__content">
      <h1 class="heading heading--big">{{ $t('shared.view.home.header') }}</h1>
      <p class="home__text">
        {{ $t('shared.view.home.info') }}
      </p>
      <router-link to="/login" class="home__link">
        <el-button type="info" class="el-button--submit">
          {{ $t('shared.view.home.login') }}
        </el-button>
      </router-link>
      <router-link to="/join">
        <el-button class="outline">
          {{ $t('shared.view.home.join') }}
        </el-button>
      </router-link>
    </section>
    <section class="home__img">
      <img
        src="@/assets/illustrations/telescope_round.png"
        alt="telescope-illustration"
      />
    </section>
  </div>
</template>

<script lang="ts">
import { Options, Vue } from 'vue-class-component';
import PublicHeader from '@/components/moderator/organisms/layout/PublicHeader.vue';

@Options({
  components: {
    PublicHeader,
  },
})
export default class Home extends Vue {}
</script>

<style lang="scss" scoped>
@import '~@/assets/styles/breakpoints.scss';

.logo-header {
  padding: 2rem;
}

.home {
  display: flex;
  flex: 1;
  align-items: center;
  box-sizing: border-box;
  flex-direction: column-reverse;
  justify-content: center;
  padding: 1rem;
  gap: 1rem;
  text-align: center;

  &__content {
    max-width: var(--app-width);

    @include md {
      max-width: calc(var(--app-width) * 0.35);
    }
  }

  &__link {
    margin: 0 auto;

    @include md {
      margin-right: 1rem;
    }
  }

  &__text {
    margin: 0.8rem 0 1.2rem;

    @include md {
      margin: 1.5rem 0 2rem;
    }
  }

  &__img {
    max-width: calc(var(--app-width) * 0.75);

    @include md {
      max-width: calc(var(--app-width) / 2);
    }
  }

  @include md {
    flex-direction: row;
    justify-content: space-between;
    padding: 2rem 4rem;
    gap: 3rem;
    text-align: left;
  }
}
</style>
