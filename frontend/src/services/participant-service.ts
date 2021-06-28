import { AxiosError } from 'axios';
import { Topic } from '@/services/topic-service';
import { apiEndpoint } from '@/services/api';
import EndpointType from '@/types/Endpoint';

const API_PARTICIPANT_ENDPOINT = apiEndpoint(EndpointType.PARTICIPANT);
const API_PARTICIPANT_CONNECT_ENDPOINT = apiEndpoint(
  EndpointType.PARTICIPANT_CONNECT
);

export enum ConnectState {
  ACTIVE = 'ACTIVE',
  FAILED = 'Failed',
}

interface Avatar {
  color: string;
  symbol: string;
}

export interface Participant {
  accessToken: string;
  avatar: Avatar;
  browserKey: string;
  id: string;
  ipHash: string;
  state: ConnectState;
}

export const connect = async (
  sessionKey: string
): Promise<Participant | Partial<Participant>> => {
  try {
    console.log('try out connection');
    const { data } = await API_PARTICIPANT_CONNECT_ENDPOINT.post<Participant>(
      `/`,
      {
        sessionKey,
      }
    );
    console.log(data);
    return data;
  } catch (error) {
    console.log('error', error);
    return (error as AxiosError).response?.data;
  }
};

export const reconnect = async (browserKey: string): Promise<Participant> => {
  try {
    const { data } = await API_PARTICIPANT_CONNECT_ENDPOINT.get<Participant>(
      `/${browserKey}/`
    );
    return data;
  } catch (error) {
    return (error as AxiosError).response?.data;
  }
};

export const getTopicList = async (): Promise<Topic[]> => {
  try {
    const { data } = await API_PARTICIPANT_ENDPOINT.get<Topic[]>(
      `/${EndpointType.TOPICS}/`
    );
    return data;
  } catch (error) {
    return (error as AxiosError).response?.data;
  }
};
