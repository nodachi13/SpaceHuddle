<template>
  <section v-if="ideas.length === 0" class="centered public-screen__error">
    <p>{{ $t('module.brainstorming.default.publicScreen.noIdeas') }}</p>
  </section>
  <div v-else class="public-screen__content">
    <section class="layout__columns">
      <IdeaCard
        v-for="(idea, index) in oldIdeas"
        :idea="idea"
        :key="index"
        :is-editable="false"
        v-model:collapseIdeas="filter.collapseIdeas"
        v-model:fadeIn="ideaTransform[idea.id]"
      />
    </section>
    <!--<section class="layout__columns new" v-if="isModerator">
      <IdeaCard
        v-for="(idea, index) in newIdeas"
        :idea="idea"
        :key="index"
        :is-editable="false"
        v-model:collapseIdeas="filter.collapseIdeas"
        v-model:fadeIn="ideaTransform[idea.id]"
      />
    </section>-->
  </div>
</template>

<script lang="ts">
import { Options, Vue } from 'vue-class-component';
import IdeaCard from '@/components/moderator/organisms/cards/IdeaCard.vue';
import * as ideaService from '@/services/idea-service';
import { Prop, Watch } from 'vue-property-decorator';
import { Idea } from '@/types/api/Idea';
import EndpointAuthorisationType from '@/types/enum/EndpointAuthorisationType';
import {
  defaultFilterData,
  FilterData,
  getFilterForTaskId,
} from '@/components/moderator/molecules/IdeaFilter.vue';

@Options({
  components: {
    IdeaCard,
  },
})

/* eslint-disable @typescript-eslint/no-explicit-any*/
export default class PublicScreen extends Vue {
  @Prop() readonly taskId!: string;
  @Prop({ default: EndpointAuthorisationType.MODERATOR })
  authHeaderTyp!: EndpointAuthorisationType;
  ideas: Idea[] = [];
  ideaTransform: { [id: string]: boolean } = {};
  readonly intervalTime = 10000;
  interval!: any;
  readonly newTimeSpan = 10000;
  filter: FilterData = { ...defaultFilterData };

  @Watch('taskId', { immediate: true })
  onTaskIdChanged(): void {
    this.getIdeas();
  }

  get isModerator(): boolean {
    return this.authHeaderTyp === EndpointAuthorisationType.MODERATOR;
  }

  get newIdeas(): Idea[] {
    const currentDate = new Date();
    return this.ideas
      .filter(
        (idea) =>
          currentDate.getTime() - new Date(idea.timestamp).getTime() <=
          this.newTimeSpan
      )
      .slice(0, 3);
  }

  get oldIdeas(): Idea[] {
    /*if (this.isModerator) {
      const currentDate = new Date();
      return this.ideas.filter(
        (idea) =>
          currentDate.getTime() - new Date(idea.timestamp).getTime() >
          this.newTimeSpan
      );
    }*/
    return this.ideas;
  }

  async getIdeas(): Promise<void> {
    const currentDate = new Date();
    if (this.taskId) {
      await getFilterForTaskId(this.taskId, this.authHeaderTyp).then(
        (filter) => {
          this.filter = filter;
        }
      );

      await ideaService
        .getIdeasForTask(
          this.taskId,
          this.filter.orderType,
          null,
          this.authHeaderTyp
        )
        .then((ideas) => {
          ideas = this.filter.orderAsc ? ideas : ideas.reverse();
          ideas = ideaService.filterIdeas(
            ideas,
            this.filter.stateFilter,
            this.filter.textFilter
          );
          this.ideas = ideas;

          this.ideaTransform = Object.assign(
            {},
            ...this.ideas.map((idea) => {
              const timeSpan =
                currentDate.getTime() - new Date(idea.timestamp).getTime();
              return { [idea.id]: timeSpan <= this.newTimeSpan };
            })
          );
        });
    }
  }

  async mounted(): Promise<void> {
    this.startInterval();
  }

  startInterval(): void {
    this.interval = setInterval(this.getIdeas, this.intervalTime);
  }

  unmounted(): void {
    clearInterval(this.interval);
  }
}
</script>

<style lang="scss" scoped>
.public-screen__content {
  //display: grid;
  //grid-template-columns: 80% 20%;
}

.new {
  padding-left: 1rem;
}
</style>
